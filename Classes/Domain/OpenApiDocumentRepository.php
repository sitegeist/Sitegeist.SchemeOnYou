<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ClassReflection;
use Neos\Flow\Reflection\ReflectionService;
use Sitegeist\SchemeOnYou\Application\OpenApiController;
use Sitegeist\SchemeOnYou\Domain\Metadata\Schema as SchemaAttribute;
use Sitegeist\SchemeOnYou\Domain\Metadata\Path as PathAttribute;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiPathCollection;
use Sitegeist\SchemeOnYou\Domain\Schema\IsCollection;
use Sitegeist\SchemeOnYou\Domain\Schema\IsSupported;
use Sitegeist\SchemeOnYou\Domain\Schema\IsValueObject;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchemaCollection;

#[Flow\Scope('singleton')]
final class OpenApiDocumentRepository
{
    /**
     * @var array<string,mixed>
     */
    #[Flow\InjectConfiguration(path: 'rootObject')]
    protected array $rootObjectConfiguration;

    /**
     * @var array<string,array{name:string, classNames: string[]}>
     */
    #[Flow\InjectConfiguration(path: 'documents')]
    protected array $documentConfiguration;

    public function __construct(
        private readonly ReflectionService $reflectionService
    ) {
    }

    public function findDocumentByName(string $name): OpenApiDocument
    {
        if (!array_key_exists($name, $this->documentConfiguration)) {
            throw new \InvalidArgumentException(sprintf('Api spec document "%s" is not configured', $name));
        }

        $documentName = $this->documentConfiguration[$name]['name'] ?? '';
        $documentClassNamePatterns = $this->documentConfiguration[$name]['classNames'];

        assert(is_string($documentName));
        assert(is_array($documentClassNamePatterns));

        $controllerClasses = [];
        $requiredSchemaClasses = [];
        $openApiControllers = $this->reflectionService->getAllSubClassNamesForClass(OpenApiController::class);

        $pathMethodsByClassName = [];
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

            $controllerClasses[] = $includeClassName;
            $classReflection = new ClassReflection($className);
            foreach ($classReflection->getMethods() as $methodReflection) {
                if (!str_ends_with($methodReflection->getName(), 'Action' )) {
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
                //$pathMethodsByClassName[$className][] = $methodReflection->name;
            }

            $requiredSchemaClasses = $this->addConstructorArgumentTypesToRequiredSchemaClasses($requiredSchemaClasses);

//            /** @var class-string $className */
//            $pathMethods = $this->reflectionService->get($className, PathAttribute::class);
//            if (!empty($pathMethods)) {
//                $pathMethodsByClassName[$className] = $pathMethods;
//            }
        }

//        \Neos\Flow\var_dump($controllerClasses);
//        \Neos\Flow\var_dump($requiredSchemaClasses);
//        die();

        return OpenApiDocument::createFromConfiguration(
            $this->rootObjectConfiguration,
            OpenApiPathCollection::fromMethodNames($pathMethodsByClassName),
            new OpenApiComponents(
                OpenApiSchemaCollection::fromClassNames($requiredSchemaClasses)
            )
        );
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
            $constructorReflection = $classReflection->getMethod('__construct');
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
