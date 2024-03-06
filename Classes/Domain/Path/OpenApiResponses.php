<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;

/**
 * @see https://swagger.io/specification/#responses-object
 */
#[Flow\Proxy(false)]
final readonly class OpenApiResponses implements \JsonSerializable
{
    /** @var array<OpenApiResponse> */
    private array $items;

    public function __construct(
        OpenApiResponse ...$items
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
            \ReflectionNamedType::class => new self(OpenApiResponse::fromClassName($returnType->getName())),
            \ReflectionUnionType::class => new self(...array_map(
                function (\ReflectionNamedType|\ReflectionIntersectionType $type): OpenApiResponse {
                    if ($type instanceof \ReflectionIntersectionType) {
                        throw new \DomainException('Cannot resolve path responses from intersection types', 1709593361);
                    }
                    $className = $type->getName();
                    return OpenApiResponse::fromClassName($className);
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
     * @return array<int,OpenApiResponse>
     */
    public function jsonSerialize(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            $result[$item->statusCode] = $item;
        }

        return $result;
    }
}
