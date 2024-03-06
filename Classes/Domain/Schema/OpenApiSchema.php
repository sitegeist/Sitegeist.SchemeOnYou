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
        public string $name,
        public string $type,
        public string $description,
        public ?array $enum = null,
        public ?array $properties = null,
        public ?array $required = null,
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
                $definitionMetadata->name ?: $reflection->getShortName(),
                'string',
                $definitionMetadata->description,
                array_map(
                    /** @phpstan-ignore-next-line parameter and return types are enforced before */
                    fn (\ReflectionEnumBackedCase $case): string => $case->getBackingValue(),
                    $reflection->getCases()
                )
            ),
            'int' => new self(
                $definitionMetadata->name ?: $reflection->getShortName(),
                'int',
                $definitionMetadata->description,
                array_map(
                    /** @phpstan-ignore-next-line parameter and return types are enforced before */
                    fn (\ReflectionEnumBackedCase $case): int => $case->getBackingValue(),
                    $reflection->getCases()
                )
            ),
            default => throw new \InvalidArgumentException(
                'Cannot create definition from non-backed enum ' . $reflection->name,
                1709499876
            ),
        };
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    public static function fromReflectionClass(\ReflectionClass $reflection): self
    {
        if (!in_array('JsonSerializable', $reflection->getInterfaceNames())) {
            throw new \DomainException(
                'Given class ' . $reflection->name . ' does not implement the required \JsonSerializable interface',
                1709503193
            );
        }

        $jsonSerializeReturnType = $reflection->getMethod('jsonSerialize')->getReturnType();
        $returnType = $jsonSerializeReturnType instanceof \ReflectionNamedType
            ? $jsonSerializeReturnType->getName()
            : null;
        $allowedReturnTypes = ['string', 'int', 'float', 'array'];
        if (!in_array($returnType, $allowedReturnTypes)) {
            throw new \DomainException(
                'Given class ' . $reflection->name . ' has invalid return type for jsonSerializable, must be one of "'
                . implode('","', $allowedReturnTypes) . '"',
                1709503874
            );
        }

        if ($returnType === 'array') {
            if (IsCollection::isSatisfiedByReflectionClass($reflection)) {
                return self::fromCollectionReflectionClass($reflection);
            } else {
                return self::fromObjectReflectionClass($reflection);
            }
        }
        $schemaMetadata = SchemaMetadata::fromReflectionClass($reflection);

        return new self(
            name: $schemaMetadata->name ?: $reflection->getShortName(),
            type: match ($returnType) {
                'string' => 'string',
                'int' => 'int',
                'float' => 'number',
                default => throw new \DomainException(
                    'Cannot resolve definition type for type ' . $returnType,
                    1709567351
                )
            },
            description: $schemaMetadata->description,
        );
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
        $definitionMetadata = SchemaMetadata::fromReflectionClass($reflectionClass);

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
            name: $definitionMetadata->name ?: $reflectionClass->getShortName(),
            type: 'object',
            description: $definitionMetadata->description,
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
