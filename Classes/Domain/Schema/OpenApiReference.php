<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\Schema as SchemaMetadata;

/**
 * @see https://swagger.io/specification/#reference-object
 */
#[Flow\Proxy(false)]
final readonly class OpenApiReference implements \JsonSerializable
{
    public function __construct(
        public string $ref,
    ) {
    }

    /**
     * @param class-string $className
     */
    public static function fromClassName(string $className): self
    {
        $reflectionClass = new \ReflectionClass($className);
        $definitionMetadata = SchemaMetadata::fromReflectionClass($reflectionClass);

        return new self('#/components/schemas/' . $definitionMetadata->name);
    }

    public function getName(): string
    {
        return str_replace('#/components/schemas/', '', $this->ref);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            '$ref' => $this->ref,
        ];
    }
}
