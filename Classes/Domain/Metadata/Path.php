<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
#[\Attribute]
final readonly class Path
{
    public function __construct(
        public string $uriPath,
        public HttpMethod $httpMethod,
    ) {
    }

    /**
     * @param \ReflectionMethod $reflection
     */
    public static function fromReflection(\ReflectionMethod $reflection): self
    {
        $pathReflections = $reflection->getAttributes(Path::class);
        if (count($pathReflections) !== 1) {
            throw new \DomainException(
                'There must be exactly one path attribute declared in method '
                . $reflection->class . '::' . $reflection->name . ', ' . count($pathReflections) . ' given',
                1709584594
            );
        }
        $arguments = $pathReflections[0]->getArguments();

        return new self(
            $arguments['uriPath'] ?? $arguments[0],
            $arguments['httpMethod'] ?? $arguments[1],
        );
    }
}
