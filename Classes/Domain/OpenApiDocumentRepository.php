<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;
use Sitegeist\SchemeOnYou\Application\OpenApiController;
use Sitegeist\SchemeOnYou\Domain\Metadata\Schema as SchemaAttribute;
use Sitegeist\SchemeOnYou\Domain\Metadata\Path as PathAttribute;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiPathCollection;
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

        $schemaAnnotatedClassesNames = $this->reflectionService->getClassNamesByAnnotation(SchemaAttribute::class);
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

            /** @var class-string $className */
            $pathMethods = $this->reflectionService->getMethodsAnnotatedWith($className, PathAttribute::class);
            if (!empty($pathMethods)) {
                $pathMethodsByClassName[$className] = $pathMethods;
            }
        }

        return OpenApiDocument::createFromConfiguration(
            $this->rootObjectConfiguration,
            OpenApiPathCollection::fromMethodNames($pathMethodsByClassName),
            new OpenApiComponents(
                OpenApiSchemaCollection::fromClassNames($schemaAnnotatedClassesNames)
            )
        );
    }
}
