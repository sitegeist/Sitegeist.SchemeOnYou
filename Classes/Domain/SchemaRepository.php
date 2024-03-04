<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;
use Sitegeist\SchemeOnYou\Domain\Definition\DefinitionCollection;
use Sitegeist\SchemeOnYou\Domain\Metadata\Definition as DefinitionMetadata;
use Sitegeist\SchemeOnYou\Domain\Metadata\Endpoint as EndpointAttribute;
use Sitegeist\SchemeOnYou\Domain\Metadata\Path as PathAttribute;
use Sitegeist\SchemeOnYou\Domain\Path\PathCollection;

#[Flow\Scope('singleton')]
final class SchemaRepository
{
    public function __construct(
        private readonly ReflectionService $reflectionService
    ) {
    }

    public function findSchema(): Schema
    {
        $definitionAnnotatedClasses = $this->reflectionService->getClassNamesByAnnotation(DefinitionMetadata::class);
        $pathAnnotatedClasses = $this->reflectionService->getClassNamesByAnnotation(EndpointAttribute::class);
        $pathMethodsByClassName = [];
        foreach ($pathAnnotatedClasses as $className) {
            /** @var class-string $className */
            $pathMethods = $this->reflectionService->getMethodsAnnotatedWith($className, PathAttribute::class);
            if (!empty($pathMethods)) {
                $pathMethodsByClassName[$className] = $pathMethods;
            }
        }

        return new Schema(
            PathCollection::fromMethodNames($pathMethodsByClassName),
            DefinitionCollection::fromClassNames($definitionAnnotatedClasses)
        );
    }
}
