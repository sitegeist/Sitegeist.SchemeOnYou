<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class Schema
{
    public function __construct(
        public string $description,
        public ?string $name = null,
    ) {
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    public static function fromReflectionClass(\ReflectionClass $reflection): self
    {
        $definitionReflections = $reflection->getAttributes(Schema::class);
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
            $arguments['name'] ?? $arguments[1] ?? $reflection->getShortName(),
        );
    }
}
