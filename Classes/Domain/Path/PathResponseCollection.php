<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class PathResponseCollection implements \JsonSerializable
{
    /** @var array<PathResponse> */
    private array $items;

    public function __construct(
        PathResponse ...$items
    ) {
        $this->items = $items;
    }

    public static function fromReflectionMethod(\ReflectionMethod $reflectionMethod): self
    {
        $returnType = $reflectionMethod->getReturnType();

        if ($returnType === null) {
            throw new \DomainException(
                'Cannot resolve path response collection from untyped method '
                . $reflectionMethod->class . '::' . $reflectionMethod->name,
                1709585438
            );
        }

        return match (get_class($returnType)) {
            \ReflectionIntersectionType::class => throw new \DomainException(
                'Cannot resolve path response collection from intersection return type of method '
                . $reflectionMethod->class . '::' . $reflectionMethod->name,
                1709585530
            ),
            \ReflectionNamedType::class => new self(PathResponse::fromClassName($returnType->getName())),
            \ReflectionUnionType::class => new self(...array_map(
                function (\ReflectionNamedType|\ReflectionIntersectionType $type): PathResponse {
                    if ($type instanceof \ReflectionIntersectionType) {
                        throw new \DomainException('Cannot resolve path responses from intersection types', 1709593361);
                    }
                    $className = $type->getName();
                    return PathResponse::fromClassName($className);
                },
                $returnType->getTypes()
            )),
            default => throw new \DomainException(
                'Cannot resolve path response collection from ' . get_class($returnType),
                1709593620
            )
        };
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    public function jsonSerialize(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            $result[$item->statusCode] = [
                'description' => $item->description,
                'schema' => $item->schema
            ];
        }

        return $result;
    }
}
