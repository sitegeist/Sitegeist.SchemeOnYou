<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\Schema as SchemaMetadata;

#[Flow\Proxy(false)]
final readonly class OpenApiSchema implements \JsonSerializable
{
    /**
     * @param array<int,int|string>|null $enum
     * @codingStandardsIgnoreStart
     * @param array<string,SchemaType|OpenApiReference|array<string,array<string,SchemaType|OpenApiReference>>> $properties
     * @codingStandardsIgnoreEnd
     * @param array<int,string> $required
     */
    public function __construct(
        public string $type,
        public ?string $name = null,
        public ?string $description = null,
        public ?array $enum = null,
        public ?array $properties = null,
        public ?array $required = null,
        public ?string $format = null,
        public ?OpenApiReference $items = null,
    ) {
    }

    /**
     * @phpstan-param class-string $className
     */
    public static function fromClassName(string $className): self
    {
        if (enum_exists($className)) {
            return self::fromReflectionEnum(new \ReflectionEnum($className));
        } elseif (class_exists($className)) {
            return self::fromReflectionClass(new \ReflectionClass($className));
        }
        throw new \DomainException('Cannot create definition from incomprehensible type ' . $className, 1709500131);
    }

    private static function fromReflectionEnum(\ReflectionEnum $reflection): self
    {
        $definitionMetadata = SchemaMetadata::fromReflectionClass($reflection);
        return match ($reflection->getBackingType()?->getName()) {
            'string' => new self(
                name: $definitionMetadata->name ?: $reflection->getShortName(),
                type: 'string',
                description: $definitionMetadata->description,
                enum: array_map(
                    /** @phpstan-ignore-next-line parameter and return types are enforced before */
                    fn(\ReflectionEnumBackedCase $case): string => $case->getBackingValue(),
                    $reflection->getCases()
                )
            ),
            'int' => new self(
                name: $definitionMetadata->name ?: $reflection->getShortName(),
                type: 'integer',
                description: $definitionMetadata->description,
                enum: array_map(
                    /** @phpstan-ignore-next-line parameter and return types are enforced before */
                    fn(\ReflectionEnumBackedCase $case): int => $case->getBackingValue(),
                    $reflection->getCases()
                )
            ),
            default => throw new \InvalidArgumentException(
                'Cannot create definition from non-backed enum ' . $reflection->name,
                1709499876
            ),
        };
    }

    public static function fromReflectionParameter(\ReflectionParameter $reflection): self
    {
        $reflectionType = $reflection->getType();
        if ($reflectionType instanceof \ReflectionNamedType) {
            $typeName = $reflectionType->getName();
            if (in_array($typeName, ['int', 'bool', 'string', 'float'])) {
                return new self(
                    type: match ($typeName) {
                        'int' => 'integer',
                        'bool' => 'boolean',
                        'string' => 'string',
                        'float' => 'number',
                        default => throw new \DomainException('Unsupported type ' . $typeName)
                    },
                );
            } elseif (in_array($typeName, [\DateTime::class, \DateTimeImmutable::class])) {
                return new self(
                    type: 'string',
                    format: 'date-time',
                );
            } elseif ($typeName === \DateInterval::class) {
                return new self(
                    type: 'string',
                    format: 'duration',
                );
            } elseif (class_exists($typeName)) {
                return self::fromClassName($typeName);
            } else {
                throw new \DomainException(sprintf('Schema can only be created for collection, value objects and backed enums "%s" is neither.', $reflection->getName()));
            }
        } elseif ($reflectionType instanceof \ReflectionUnionType) {
            throw new \DomainException(sprintf('Schema can only be created for collection, value objects and backed enums "%s" is neither.', $reflection->getName()));
        } else {
            throw new \DomainException(sprintf('Schema can only be created for collection, value objects and backed enums "%s" is neither.', $reflection->getName()));
        }
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    public static function fromReflectionClass(\ReflectionClass $reflection): self
    {
        if ($reflection->isEnum() && $reflection->isSubclassOf(\BackedEnum::class)) {
            return self::fromReflectionEnum(new \ReflectionEnum($reflection->getName()));
        } elseif (in_array($reflection->getName(), [\DateTime::class, \DateTimeImmutable::class])) {
            return new self(
                type: 'string',
                format: 'date-time',
            );
        } elseif ($reflection->getName() === \DateInterval::class) {
            return new self(
                type: 'string',
                format: 'duration',
            );
        } elseif (IsDataTransferObjectCollection::isSatisfiedByReflectionClass($reflection)) {
            return self::fromCollectionReflectionClass($reflection);
        } elseif (IsDataTransferObject::isSatisfiedByReflectionClass($reflection)) {
            return self::fromObjectReflectionClass($reflection);
        }
        throw new \DomainException(sprintf('Schema can only be created for collection, value objects and backed enums "%s" is neither.', $reflection->getName()));
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private static function fromCollectionReflectionClass(\ReflectionClass $reflection): self
    {
        $definitionMetadata = SchemaMetadata::fromReflectionClass($reflection);
        /** @var \ReflectionNamedType $parameterType */
        $parameterType = ($reflection->getConstructor()?->getParameters() ?: [])[0]->getType();
        /** @var class-string $parameterClassName */
        $parameterClassName = $parameterType->getName();
        $parameterSchema = self::fromReflectionClass(new \ReflectionClass($parameterClassName));

        return new self(
            name: $definitionMetadata->name ?: $reflection->getShortName(),
            type: 'array',
            description: $definitionMetadata->description,
            items: $parameterSchema->toReference()
        );
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private static function fromObjectReflectionClass(\ReflectionClass $reflectionClass): self
    {
        $schemaMetadata = SchemaMetadata::fromReflectionClass($reflectionClass);

        $constructorParameters = $reflectionClass->getConstructor()?->getParameters() ?: [];
        if (count($constructorParameters) === 1) {
            $singleConstructorParameter = $constructorParameters[array_key_first($constructorParameters)];
            if (
                $singleConstructorParameter->getType() instanceof \ReflectionNamedType
                && $singleConstructorParameter->name === 'value'
            ) {
                return new self(
                    name: $schemaMetadata->name ?: $reflectionClass->getShortName(),
                    type: match ($singleConstructorParameter->getType()->getName()) {
                        'string', 'DateTimeImmutable', 'DateTime', 'DateInterval' => 'string',
                        'int' => 'integer',
                        'float' => 'number',
                        'bool' => 'boolean',
                        default => throw new \DomainException(
                            'Unsupported type ' . $singleConstructorParameter->getType()->getName()
                            . ' for single constructor parameter "' . $singleConstructorParameter->name . '"'
                            . ' of class ' . $reflectionClass->name
                        )
                    },
                    description: $schemaMetadata->description,
                    format: match ($singleConstructorParameter->getType()->getName()) {
                        'DateTimeImmutable' => 'date-time',
                        'DateInterval' => 'duration',
                        default => null
                    },
                );
            }
        }

        $properties = [];
        $required = [];
        foreach ($reflectionClass->getConstructor()?->getParameters() ?: [] as $reflectionParameter) {
            $type = $reflectionParameter->getType();
            if ($type === null) {
                throw new \DomainException(
                    'Cannot resolve schema reference for untyped constructor parameter '
                    . $reflectionParameter->name . ' of class ' . $reflectionClass->name,
                    1709718001
                );
            }
            $properties[$reflectionParameter->name] = match (get_class($type)) {
                \ReflectionNamedType::class => SchemaType::selfOrReferenceFromReflectionNamedType($type),
                \ReflectionUnionType::class => [
                    'oneOf' => array_map(
                        fn (\ReflectionType $singleType): SchemaType|OpenApiReference
                            => match (get_class($singleType)) {
                                \ReflectionIntersectionType::class,
                                    => throw new \DomainException(
                                        'Cannot resolve schema reference from intersection type'
                                        . ' given for constructor parameter'
                                        . $reflectionParameter->name . ' of class ' . $reflectionClass->name,
                                        1709560366
                                    ),
                                \ReflectionNamedType::class => SchemaType::selfOrReferenceFromReflectionNamedType(
                                    $singleType
                                ),
                                default => throw new \DomainException('wat')
                            },
                        $type->getTypes()
                    )
                ],
                \ReflectionIntersectionType::class => throw new \DomainException(
                    'Cannot resolve schema reference from intersection type given for constructor parameter'
                    . $reflectionParameter->name . ' of class ' . $reflectionClass->name,
                    1709560366
                ),
                default => throw new \DomainException(
                    'Cannot resolve schema reference for untyped constructor parameter '
                    . $reflectionParameter->name . ' of class ' . $reflectionClass->name,
                    1709718001
                )
            };
            $properties[$reflectionParameter->name] = SchemaType::selfOrReferenceFromReflectionParameter(
                $reflectionParameter
            );
            if (!$reflectionParameter->isDefaultValueAvailable()) {
                $required[] = $reflectionParameter->name;
            }
        }

        return new self(
            name: $schemaMetadata->name ?: $reflectionClass->getShortName(),
            type: 'object',
            description: $schemaMetadata->description,
            properties: $properties,
            required: $required
        );
    }

    public function toReference(): OpenApiReference
    {
        return new OpenApiReference('#/components/schemas/' . $this->name);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this));
    }
}
