<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Mvc\Routing\Dto\ResolveContext;
use Neos\Flow\Mvc\Routing\Dto\RouteContext;
use Neos\Flow\Mvc\Routing\Dto\RouteParameters;
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Flow\Reflection\ClassReflection;
use Neos\Flow\Reflection\MethodReflection;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Http\Factories\UriFactory;
use Neos\Utility\Arrays;
use Psr\Http\Message\UriFactoryInterface;
use Sitegeist\SchemeOnYou\Application\OpenApiController;
use Sitegeist\SchemeOnYou\Domain\Metadata\HttpMethod;
use Sitegeist\SchemeOnYou\Domain\Metadata\Parameter;
use Sitegeist\SchemeOnYou\Domain\Metadata\RequestBody;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiParameter;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiParameterCollection;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiPathCollection;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiPathItem;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiRequestBody;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiResponse;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiResponses;
use Sitegeist\SchemeOnYou\Domain\Path\PathDefinition;
use Sitegeist\SchemeOnYou\Domain\Schema\IsSupported;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchemaCollection;
use Neos\Flow\Mvc\Routing\Router;

class OpenApiDocumentFactory
{
    public function __construct(
        private readonly ReflectionService $reflectionService,
        private readonly Router $router,
        private readonly ObjectManager $objectManager,
        private readonly UriFactoryInterface $uriFactory,
    ) {
    }

    public function createOpenApiDocumentFromNameAncClassNamePattern(string $documentName, array $documentClassNamePatterns, array $rootObjectConfiguration): OpenApiDocument
    {
        $requiredSchemaClasses = [];
        $openApiControllers = $this->reflectionService->getAllSubClassNamesForClass(OpenApiController::class);

        $pathes = new OpenApiPathCollection();

        foreach ($openApiControllers as $className) {
            // only include classes that match the $classNamePatterns
            $includeClassName = false;
            foreach ($documentClassNamePatterns as $documentClassNamePattern) {
                if (fnmatch($documentClassNamePattern, $className, FNM_NOESCAPE)) {
                    $includeClassName = true;
                    break;
                }
            }
            if ($includeClassName === false) {
                continue;
            }

            $classReflection = new ClassReflection($className);
            foreach ($classReflection->getMethods() as $methodReflection) {
                if (!str_ends_with($methodReflection->getName(), 'Action')) {
                    continue;
                }
                $methodReturnType = $methodReflection->getReturnType();
                if ($methodReturnType instanceof \ReflectionNamedType && IsSupported::isSatisfiedByReflectionType($methodReturnType)) {
                    $requiredSchemaClasses[] = $methodReturnType->getName();
                }
                foreach ($methodReflection->getParameters() as $parameterReflection) {
                    $parameterType = $parameterReflection->getType();
                    if ($parameterType instanceof \ReflectionNamedType) {
                        if (in_array($parameterType->getName(), ['int', 'bool', 'string', 'float', \DateTime::class, \DateTimeInterface::class, \DateInterval::class])) {
                            continue;
                        } elseif (IsSupported::isSatisfiedByReflectionType($parameterType)) {
                            $requiredSchemaClasses[] = $parameterType->getName();
                        }
                    }
                }
                $pathes = $pathes->merge($this->createPathesFromPathAndMethodReflection($classReflection, $methodReflection));
            }

            $requiredSchemaClasses = $this->addConstructorArgumentTypesToRequiredSchemaClasses($requiredSchemaClasses);
        }

        $rootObjectConfiguration = Arrays::arrayMergeRecursiveOverrule($rootObjectConfiguration, [
            'info' => [
                'title' => $documentName
            ]
        ]);

        return OpenApiDocument::createFromConfiguration(
            $rootObjectConfiguration,
            $pathes,
            new OpenApiComponents(
                OpenApiSchemaCollection::fromClassNames($requiredSchemaClasses)
            )
        );
    }

    private function createPathesFromPathAndMethodReflection(ClassReflection $classReflection, MethodReflection $methodReflection): OpenApiPathCollection
    {
        /**
         * @var OpenApiPathItem[] $pathes
         */
        $pathes = [];

        $className = $classReflection->getName();
        $methodName = $methodReflection->getName();

        $controllerObjectName = $this->objectManager->getCaseSensitiveObjectName($className);
        $controllerPackageKey = $this->objectManager->getPackageKeyByObjectName($controllerObjectName);
        $controllerPackageNamespace = str_replace('.', '\\', $controllerPackageKey);
        if (!str_ends_with($className, 'Controller')) {
            throw new \DomainException('Only for controller classes');
        }

        $localClassName = substr($className, strlen($controllerPackageNamespace) + 1);

        if (str_starts_with($localClassName, 'Controller\\')) {
            $controllerName = substr($localClassName, 11);
            $subPackage = null;
        } elseif (str_contains($localClassName, '\\Controller\\')) {
            list($subPackage, $controllerName) = explode('\\Controller\\', $localClassName);
        } else {
            throw new \DomainException('Unknown controller pattern');
        }

        if (!str_ends_with($methodName, 'Action')) {
            throw new \DomainException('Only for action methods');
        }

        $controller = substr($controllerName, 0, -10);
        $action = substr($methodName, 0, -6);

        $resolveContext = new ResolveContext(
            $this->uriFactory->createUri('http://localhost'),
            [
                '@package' => $controllerPackageKey,
                '@subpackage' => $subPackage,
                '@controller' => $controller,
                '@action' => $action,
            ],
            false,
            '',
            RouteParameters::createEmpty()->withParameter('requestUriHost', 'localhost')
        );

        $requestBody = null;
        $parameters = [];
        foreach ($methodReflection->getParameters() as $reflectionParameter) {
            $parameterProcessed = false;
            foreach ($reflectionParameter->getAttributes() as $attribute) {
                if ($attribute->getName() === RequestBody::class) {
                    if ($parameterProcessed) {
                        throw new \DomainException(
                            'Method parameter ' . $methodReflection->getDeclaringClass()->name
                            . '::' . $methodReflection->getName() . '::' . $reflectionParameter->name
                            . ' must be attributed as either OpenAPI Parameter or RequestBody'
                            . ' and was already attributed'
                        );
                    }
                    if ($requestBody !== null) {
                        throw new \DomainException(
                            'Only one parameter can be resolved via request body',
                            1710069260
                        );
                    }
                    $requestBody = OpenApiRequestBody::fromReflectionParameter($reflectionParameter);
                    $parameterProcessed = true;
                } elseif ($attribute->getName() === Parameter::class) {
                    if ($parameterProcessed) {
                        throw new \DomainException(
                            'Method parameter ' . $methodReflection->getDeclaringClass()->name
                            . '::' . $methodReflection->getName() . '::' . $reflectionParameter->name
                            . ' must be attributed as either OpenAPI Parameter or RequestBody'
                            . ' and was already attributed'
                        );
                    }
                    $parameters[] = OpenApiParameter::fromReflectionParameter($reflectionParameter);
                    $parameterProcessed = true;
                }
            }
            if (!$parameterProcessed) {
                throw new \DomainException(
                    'Method parameter ' . $methodReflection->getDeclaringClass()->name
                    . '::' . $methodReflection->getName() . '::' . $reflectionParameter->name
                    . ' must be attributed as either OpenAPI Parameter or RequestBody but was not attributed at all'
                );
            }
        }

        foreach ($this->router->getRoutes() as $route) {
            if ($route->resolves($resolveContext)) {
                foreach ($route->getHttpMethods() as $httpMethod) {
                    $pathes[] = new OpenApiPathItem(
                        new PathDefinition('/' . $route->getUriPattern()),
                        HttpMethod::from(strtolower($httpMethod)),
                        new OpenApiParameterCollection(...$parameters),
                        $requestBody,
                        OpenApiResponses::fromReflectionMethod($methodReflection)
                    );
                }
            }
        }

        return new OpenApiPathCollection(...$pathes);
    }

    /**
     * Find all classes that are used in constructor arguments and add those to the required schemas
     */
    private function addConstructorArgumentTypesToRequiredSchemaClasses(array $requiredSchemaClasses): array
    {
        $classesToCheckStack = $requiredSchemaClasses;
        while (count($classesToCheckStack) > 0) {
            $className = array_shift($classesToCheckStack);
            $classReflection = new ClassReflection($className);
            $constructorReflection = $classReflection->getConstructor();
            foreach ($constructorReflection->getParameters() as $constructorParameter) {
                $parameterType = $constructorParameter->getType();
                if ($parameterType instanceof \ReflectionNamedType) {
                    $parameterTypeName = $parameterType->getName();
                    if (in_array($parameterTypeName, ['int', 'bool', 'string', 'float', \DateTimeImmutable::class, \DateTime::class, \DateTimeInterface::class, \DateInterval::class])) {
                        continue;
                    }
                    if (in_array($parameterTypeName, $requiredSchemaClasses)) {
                        continue;
                    }
                    if (class_exists($parameterTypeName) && IsSupported::isSatisfiedByReflectionType($parameterType)) {
                        $requiredSchemaClasses[] = $parameterTypeName;
                        $classesToCheckStack[] = $parameterTypeName;
                    } else {
                        throw new \DomainException(sprintf('Parameter %s has unsupported type %s in class %s', $constructorParameter->getName(), $parameterTypeName, $className));
                    }
                } elseif ($parameterType instanceof \ReflectionUnionType) {
                    foreach ($parameterType->getTypes() as $parameterSubType) {
                        if ($parameterSubType instanceof \ReflectionNamedType) {
                            $parameterSubtypeName = $parameterSubType->getName();
                            if (in_array($parameterSubtypeName, $requiredSchemaClasses)) {
                                continue;  // already checked
                            }
                            if (class_exists($parameterSubtypeName) && IsSupported::isSatisfiedByReflectionType($parameterSubtypeName)) {
                                $requiredSchemaClasses[] = $parameterSubtypeName;
                                $classesToCheckStack[] = $parameterSubtypeName;
                            }
                        } else {
                            throw new \DomainException(sprintf('Parameter %s has unsupported type %s in class %s', $constructorParameter->getName(), $parameterTypeName, $className));
                        }
                    }
                } elseif ($parameterType instanceof \ReflectionIntersectionType) {
                    throw new \DomainException(sprintf('Parameter %s has intersection which is unsupported in class %s', $constructorParameter->getName(), $className));
                } elseif ($parameterType === null) {
                    throw new \DomainException(sprintf('Parameter %s has no type in class %s', $constructorParameter->getName(), $className));
                }
            }
        }
        return $requiredSchemaClasses;
    }
}
