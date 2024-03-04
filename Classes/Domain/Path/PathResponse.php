<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\Definition as DefinitionAttribute;
use Sitegeist\SchemeOnYou\Domain\Metadata\PathResponse as PathResponseAttribute;

#[Flow\Proxy(false)]
final readonly class PathResponse implements \JsonSerializable
{
    /**
     * @param array<string,mixed> $schema
     */
    public function __construct(
        public int $statusCode,
        public string $description,
        public array $schema,
    ) {
    }

    public static function fromClassName(string $className): self
    {
        if (!class_exists($className)) {
            throw new \DomainException('Cannot resolve path responses from non-class strings', 1709593290);
        }
        $reflectionClass = new \ReflectionClass($className);

        $definitionAttribute = DefinitionAttribute::fromReflectionClass($reflectionClass);
        $pathResponseAttribute = PathResponseAttribute::fromReflectionClass($reflectionClass);

        return new self(
            statusCode: $pathResponseAttribute->statusCode,
            description: $pathResponseAttribute->description,
            schema: $definitionAttribute->toReferenceType()
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
