<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\Response as PathResponseAttribute;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchema;

/**
 * @see https://swagger.io/specification/#response-object
 */
#[Flow\Proxy(false)]
final readonly class OpenApiResponse implements \JsonSerializable
{
    /**
     * @param array<string,mixed> $content
     */
    public function __construct(
        public int $statusCode,
        public string $description,
        public array $content,
    ) {
    }

    public static function fromClassName(string $className): self
    {
        if (!class_exists($className)) {
            throw new \DomainException('Cannot resolve path responses from non-class strings', 1709593290);
        }
        $reflectionClass = new \ReflectionClass($className);
        $schema = OpenApiSchema::fromReflectionClass($reflectionClass);
        $pathResponseAttribute = PathResponseAttribute::fromReflectionClass($reflectionClass);

        return new self(
            statusCode: $pathResponseAttribute->statusCode,
            description: $pathResponseAttribute->description,
            content: [
                'application/json' => [
                    'schema' => $schema->toReference()
                ]
            ]
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'description' => $this->description,
            'content' => $this->content
        ];
    }
}
