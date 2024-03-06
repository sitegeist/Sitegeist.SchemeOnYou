<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class OpenApiSchemaCollection implements \JsonSerializable
{
    /** @var array<OpenApiSchema> */
    private array $items;

    public function __construct(
        OpenApiSchema ...$items
    ) {
        $this->items = $items;
    }

    /**
     * @param array<class-string> $classNames
     */
    public static function fromClassNames(array $classNames): self
    {
        return new self(...array_map(
            fn (string $className): OpenApiSchema => OpenApiSchema::fromClassName($className),
            $classNames
        ));
    }

    /**
     * @return array<string, OpenApiSchema>
     */
    public function jsonSerialize(): array
    {
        $result = [];
        foreach ($this->items as $schema) {
            $result[$schema->name] = $schema;
        }

        return $result;
    }
}
