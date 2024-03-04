<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;
use Sitegeist\SchemeOnYou\Domain\Metadata\Definition as DefinitionMetadata;
use Sitegeist\SchemeOnYou\Domain\Metadata\Path as PathMetadata;

#[Flow\Scope('singleton')]
final class SchemaRepository
{

    public function __construct(
        private readonly ReflectionService $reflectionService
    ) {
    }

    public function findSchema(): Schema
    {
        $pathAnnotatedClasses = $this->reflectionService->getClassNamesByAnnotation(PathMetadata::class);
        $definitionAnnotatedClasses = $this->reflectionService->getClassNamesByAnnotation(DefinitionMetadata::class);

        return new Schema(
            PathCollection::fromClassNames($pathAnnotatedClasses),
            DefinitionCollection::fromClassNames($definitionAnnotatedClasses)
        );
    }
}
