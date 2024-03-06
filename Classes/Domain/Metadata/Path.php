<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Path\PathDefinition;

#[Flow\Proxy(false)]
#[\Attribute]
final readonly class Path
{
    public function __construct(
        public PathDefinition $pathDefinition,
        public HttpMethod $httpMethod,
    ) {
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     */
    public static function fromReflectionMethod(\ReflectionMethod $reflectionMethod): self
    {
        $pathReflections = $reflectionMethod->getAttributes(Path::class);
        if (count($pathReflections) !== 1) {
            throw new \DomainException(
                'There must be exactly one path attribute declared in method '
                . $reflectionMethod->class . '::' . $reflectionMethod->name . ', ' . count($pathReflections) . ' given',
                1709584594
            );
        }
        $arguments = $pathReflections[0]->getArguments();

        return new self(
            $arguments['pathDefinition'] ?? $arguments[0],
            $arguments['httpMethod'] ?? $arguments[1],
        );
    }
}
