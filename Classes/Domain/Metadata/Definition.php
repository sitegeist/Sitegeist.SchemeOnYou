<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
#[\Attribute]
final readonly class Definition
{
    public function __construct(
        public string $description,
        public ?string $name = null,
    ) {
    }

    public static function fromReflection(\ReflectionClass|\ReflectionEnum $reflection): self
    {
        $definitionReflections = $reflection->getAttributes(Definition::class);
        if (count($definitionReflections) !== 1) {
            throw new \DomainException(
                'There must be exactly one definition attribute declared in class ' . $reflection->name
                . count($definitionReflections) . ' given',
                1709537723
            );
        }
        $arguments = $definitionReflections[0]->getArguments();

        return new self(
            $arguments['description'] ?? $arguments[0],
            $arguments['name'] ?? $arguments[1] ?? null,
        );
    }
}
