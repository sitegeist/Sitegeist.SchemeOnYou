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

    public function __construct(
        private readonly ReflectionService $reflectionService
    ) {
    }

    public function findDocument(): OpenApiDocument
    {
        $schemaAnnotatedClassesNames = $this->reflectionService->getClassNamesByAnnotation(SchemaAttribute::class);
        $openApiControllers = $this->reflectionService->getAllSubClassNamesForClass(OpenApiController::class);
        $pathMethodsByClassName = [];
        foreach ($openApiControllers as $className) {
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
