<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Definition;

use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\UuidInterface;
use Sitegeist\SchemeOnYou\Domain\Metadata\Definition as DefinitionMetadata;

#[Flow\Proxy(false)]
final readonly class SchemaType implements \JsonSerializable
{
    /**
     * @param array<string,mixed> $typeDeclaration
     */
    public function __construct(
        public array $typeDeclaration,
    ) {
    }

    public static function fromReflectionParameter(\ReflectionParameter $reflectionParameter): self
    {
        $type = $reflectionParameter->getType();

        return match (true) {
            $type instanceof \ReflectionNamedType => self::fromReflectionNamedType($type),
            $type instanceof \ReflectionUnionType => self::fromReflectionUnionType($type),
            $type instanceof \ReflectionIntersectionType => throw new \DomainException(
                'Cannot resolve schema type from intersection type given for parameter '
                . $reflectionParameter->name,
                1709560366
            ),
            default => throw new \DomainException(
                'Cannot resolve schema type for untyped parameter ' . $reflectionParameter->name
            )
        };
    }

    public static function fromReflectionNamedType(\ReflectionNamedType $reflectionType): self
    {
        $type = match ($reflectionType->getName()) {
            'bool', 'boolean' => [
                'type' => 'boolean'
            ],
            'string' => [
                'type' => 'string'
            ],
            'int', 'integer' => [
                'type' => 'integer'
            ],
            'float' => [
                'type' => 'number'
            ],
            'DateTimeImmutable' => [
                'type' => 'string',
                'format' => 'date-time'
            ],
            'DateInterval' => [
                'type' => 'string',
                'format' => 'duration'
            ],
            UriInterface::class => [
                'type' => 'string',
                'format' => 'uri'
            ],
            UuidInterface::class => [
                'type' => 'string',
                'format' => 'uuid'
            ],
            default => match (true) {
                class_exists($reflectionType->getName()), enum_exists($reflectionType->getName())
                    => DefinitionMetadata::fromReflection(
                        new \ReflectionClass($reflectionType->getName())
                    )->toReferenceType(),
                default => throw new \DomainException(
                    'Cannot resolve schema type for type ' . $reflectionType->getName(),
                    1709560846
                )
            }
        };

        return $reflectionType->allowsNull()
            ? new self([
                'oneOf' => [
                    $type,
                    [
                        'type' => 'null'
                    ]
                ]
            ])
            : new self($type);
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    public static function fromReflectionClass(\ReflectionClass $reflectionClass): self
    {
        return new self(DefinitionMetadata::fromReflection($reflectionClass)->toReferenceType());
    }

    public static function fromReflectionUnionType(\ReflectionUnionType $reflectionUnionType): self
    {
        $types = [];
        foreach ($reflectionUnionType->getTypes() as $reflectionType) {
            if (!$reflectionType instanceof \ReflectionNamedType) {
                throw new \DomainException(
                    'Cannot resolve schema type from intersection type given',
                    1709560366
                );
            }
            $types[] = self::fromReflectionNamedType($reflectionType);
        }
        if ($reflectionUnionType->allowsNull()) {
            $types[] = [
                'type' => 'null'
            ];
        }

        return new self([
            'oneOf' => $types
        ]);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->typeDeclaration;
    }
}
