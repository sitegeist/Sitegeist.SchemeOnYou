<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Core\Bootstrap;
use Neos\Flow\Reflection\ReflectionService;
use Sitegeist\SchemeOnYou\Domain\Metadata\Schema as SchemaMetadata;
use Sitegeist\SchemeOnYou\Domain\Metadata\StringProperty;
use Sitegeist\SchemeOnYou\Infrastructure\InterfaceImplementationDetector;

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
        public ?bool $additionalProperties = null,
        public ?array $required = null,
        public ?string $format = null,
        public ?OpenApiReference $items = null,
        public ?OpenApiSchemaOrReferenceCollection $oneOf = null,
        public ?OpenApiSchemaOrReferenceCollection $anyOf = null,
        public ?OpenApiSchemaOrReferenceCollection $allOf = null,
        public ?OpenApiSchemaDiscriminator $discriminator = null,
    ) {
    }

    /**
     * @phpstan-param string $typeName
     */
    public static function fromTypeName(string $typeName): self
    {
        if (enum_exists($typeName)) {
            return self::fromReflectionEnum(new \ReflectionEnum($typeName));
        } elseif (class_exists($typeName)) {
            return self::fromReflectionClass(new \ReflectionClass($typeName));
        } elseif (interface_exists($typeName)) {
            return self::fromInterfaceReflectionClass(new \ReflectionClass($typeName));
        }
        throw new \DomainException('Cannot create definition from incomprehensible type ' . $typeName, 1709500131);
    }


    private static function fromReflectionEnum(\ReflectionEnum $reflection): self
    {
        $definitionMetadata = SchemaMetadata::fromReflectionClass($reflection);
        return match ($reflection->getBackingType()?->getName()) {
            'string' => new self(
                type: 'string',
                name: $definitionMetadata->name ?: $reflection->getShortName(),
                description: $definitionMetadata->description,
                enum: array_map(
                    /** @phpstan-ignore-next-line parameter and return types are enforced before */
                    fn(\ReflectionEnumBackedCase $case): string => $case->getBackingValue(),
                    $reflection->getCases()
                )
            ),
            'int' => new self(
                type: 'integer',
                name: $definitionMetadata->name ?: $reflection->getShortName(),
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
                    },
                    format: match ($typeName) {
                        'string' => StringProperty::tryfromReflectionParameter($reflection)?->format ?: null,
                        default => null,
                    }
                );
            } elseif (in_array($typeName, [\DateTime::class, \DateTimeImmutable::class])) {
                $propertyAttribute = StringProperty::tryfromReflectionParameter($reflection);
                return new self(
                    type: 'string',
                    format: $propertyAttribute?->format ?: 'date-time',
                );
            } elseif ($typeName === \DateInterval::class) {
                return new self(
                    type: 'string',
                    format: 'duration',
                );
            }
            return self::fromReflectionNamedType($reflectionType);
        } elseif ($reflectionType instanceof \ReflectionUnionType) {
            return self::fromReflectionUnionType($reflectionType);
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
        } elseif (interface_exists($reflection->getName())) {
            // @todo is this still used
            return self::fromInterfaceReflectionClass($reflection);
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
            type: 'array',
            name: $definitionMetadata->name ?: $reflection->getShortName(),
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
                $propertyAttribute = StringProperty::tryfromReflectionParameter($singleConstructorParameter);
                return new self(
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
                    name: $schemaMetadata->name ?: $reflectionClass->getShortName(),
                    description: $schemaMetadata->description,
                    format: match ($singleConstructorParameter->getType()->getName()) {
                        'DateTimeImmutable' => $propertyAttribute?->format ?: 'date-time',
                        'DateInterval' => 'duration',
                        'string' => $propertyAttribute?->format ?: null,
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
                \ReflectionNamedType::class => SchemaType::selfOrReferenceFromReflectionNamedType(
                    $type,
                    $reflectionParameter
                ),
                \ReflectionUnionType::class => SchemaType::fromReflectionUnionType(
                    $type,
                    $reflectionParameter
                ),
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

        if (array_key_exists(OpenApiSchemaDiscriminator::DISCRIMINATOR_NAME, $properties)) {
            throw new \DomainException(sprintf('Object "%s" must not specify reserved property "%s".', $reflectionClass->getName(), OpenApiSchemaDiscriminator::DISCRIMINATOR_NAME), 1774439490);
        }

        $properties[OpenApiSchemaDiscriminator::DISCRIMINATOR_NAME] = self::discriminatorPropertyTypeForClassName($reflectionClass->getName());

        return new self(
            type: 'object',
            name: $schemaMetadata->name ?: $reflectionClass->getShortName(),
            description: $schemaMetadata->description,
            properties: $properties,
            additionalProperties: false,
            required: $required
        );
    }

    private static function fromReflectionNamedType(\ReflectionNamedType $reflectionType): self
    {
        return self::fromTypeName($reflectionType->getName());
    }

    private static function fromReflectionUnionType(\ReflectionUnionType $reflection): self
    {
        $subSchemas = [];
        foreach ($reflection->getTypes() as $type) {
            if ($type instanceof \ReflectionNamedType) {
                $subSchemas[] = new OpenApiSchema(
                    type: 'object',
                    allOf: new OpenApiSchemaOrReferenceCollection(
                        self::discriminatorForClassName($type->getName()),
                        OpenApiReference::fromClassName($type->getName())
                    )
                );
            } else {
                throw new \DomainException('Union types are only supported for named types. ' . get_class($type) . ' given');
            }
        }

        return new self(
            type: 'object',
            oneOf: new OpenApiSchemaOrReferenceCollection(...$subSchemas),
            discriminator: new OpenApiSchemaDiscriminator()
        );
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private static function fromInterfaceReflectionClass(\ReflectionClass $reflectionClass): self
    {
        $schemaMetadata = SchemaMetadata::fromReflectionClass($reflectionClass);

        $detector = new InterfaceImplementationDetector();
        $implementationClasses = $detector->detect($reflectionClass->name);

        $implementationSchemas = [];
        foreach ($implementationClasses as $implementationClass) {
            $implementationSchemas[] = new OpenApiSchema(
                type: 'object',
                allOf: new OpenApiSchemaOrReferenceCollection(
                    self::discriminatorForClassName($implementationClass),
                    OpenApiReference::fromClassName($implementationClass)
                )
            );
        }

        return new self(
            type: 'object',
            name: $schemaMetadata->name ?: $reflectionClass->getShortName(),
            description: $schemaMetadata->description,
            oneOf: new OpenApiSchemaOrReferenceCollection(...$implementationSchemas),
            discriminator: new OpenApiSchemaDiscriminator()
        );
    }

    public static function discriminatorForClassName(string $className): self
    {
        return new self(
            type: 'object',
            required: [OpenApiSchemaDiscriminator::DISCRIMINATOR_NAME]
        );
    }

    public static function discriminatorPropertyTypeForClassName(string $className): SchemaType
    {
        return new SchemaType(
            [
                'type' => 'string',
                'enum' => [str_replace('\\', '_', $className)]
            ]
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
        return array_filter(get_object_vars($this), fn ($value) => $value !== null);
    }
}
