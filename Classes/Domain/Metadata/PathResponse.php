<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class PathResponse
{
    public function __construct(
        public int $statusCode,
        public string $description,
    ) {
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    public static function fromReflectionClass(\ReflectionClass $reflection): self
    {
        $pathResponseAttributes = $reflection->getAttributes(self::class);
        if (count($pathResponseAttributes) !== 1) {
            throw new \DomainException(
                'There must be exactly one path response attribute declared in class '
                . $reflection->name . ', ' . count($pathResponseAttributes) . ' given',
                1709587611
            );
        }
        $arguments = $pathResponseAttributes[0]->getArguments();

        return new self(
            $arguments['statusCode'] ?? $arguments[0],
            $arguments['description'] ?? $arguments[1],
        );
    }
}
