<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\StringProperty;

#[Flow\Proxy(false)]
final readonly class SchemaType implements \JsonSerializable
{
    /**
     * @param array<string,mixed>|OpenApiReference $typeDeclaration
     */
    public function __construct(
        public array|OpenApiReference $typeDeclaration,
    ) {
    }

    public static function selfOrReferenceFromReflectionParameter(
        \ReflectionParameter $reflectionParameter
    ): self|OpenApiReference {
        $type = $reflectionParameter->getType();

        return match (true) {
            $type instanceof \ReflectionNamedType => self::selfOrReferenceFromReflectionNamedType($type, $reflectionParameter),
            $type instanceof \ReflectionUnionType => self::fromReflectionUnionType($type, $reflectionParameter),
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

    public static function selfOrReferenceFromReflectionNamedType(
        \ReflectionNamedType $reflectionType,
        \ReflectionParameter $reflectionParameter,
    ): self|OpenApiReference {
        $type = match ($reflectionType->getName()) {
            'null' => [
                'type' => 'null'
            ],
            'bool', 'boolean' => [
                'type' => 'boolean'
            ],
            'string' => array_filter([
                'type' => 'string',
                'description' => StringProperty::tryFromReflectionParameter($reflectionParameter)?->description
            ]),
            'int', 'integer' => [
                'type' => 'integer'
            ],
            'float' => [
                'type' => 'number'
            ],
            'array' => [
                'type' => 'array'
            ],
            'DateTimeImmutable' => [
                'type' => 'string',
                'format' => StringProperty::tryFromReflectionParameter($reflectionParameter)?->format ?: 'date-time'
            ],
            'DateInterval' => [
                'type' => 'string',
                'format' => 'duration'
            ],
            default => match (true) {
                class_exists($reflectionType->getName()), enum_exists($reflectionType->getName())
                    => OpenApiSchema::fromClassName($reflectionType->getName())->toReference(),
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
            : ($type instanceof OpenApiReference ? $type : new self($type));
    }

    public static function fromReflectionUnionType(\ReflectionUnionType $reflectionUnionType, \ReflectionParameter $reflectionParameter): self
    {
        $types = [];
        foreach ($reflectionUnionType->getTypes() as $reflectionType) {
            if (!$reflectionType instanceof \ReflectionNamedType) {
                throw new \DomainException(
                    'Cannot resolve schema type from intersection type given',
                    1709560366
                );
            }
            $types[] = self::selfOrReferenceFromReflectionNamedType($reflectionType, $reflectionParameter);
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
     * @return array<string,mixed>|OpenApiReference
     */
    public function jsonSerialize(): array|OpenApiReference
    {
        return $this->typeDeclaration;
    }
}
