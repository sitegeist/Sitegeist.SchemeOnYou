<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Definition;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\Definition as DefinitionMetadata;

#[Flow\Proxy(false)]
final readonly class Definition implements \JsonSerializable
{
    /**
     * @param array<int,int|string>|null $enum
     * @param array<string,SchemaType> $properties
     * @param array<int,string> $required
     * @param array<string,string> $items
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $description,
        public ?array $enum = null,
        public ?array $properties = null,
        public ?array $required = null,
        public ?array $items = null,
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
        $definitionMetadata = DefinitionMetadata::fromReflectionClass($reflection);
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
        $definitionMetadata = DefinitionMetadata::fromReflectionClass($reflection);

        return new self(
            name: $definitionMetadata->name ?: $reflection->getShortName(),
            type: match ($returnType) {
                'string' => 'string',
                'int' => 'int',
                'float' => 'number',
                default => throw new \DomainException(
                    'Cannot resolve definition type for type ' . $returnType,
                    1709567351
                )
            },
            description: $definitionMetadata->description,
        );
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private static function fromCollectionReflectionClass(\ReflectionClass $reflection): self
    {
        $definitionMetadata = DefinitionMetadata::fromReflectionClass($reflection);
        /** @var \ReflectionNamedType $parameterType */
        $parameterType = ($reflection->getConstructor()?->getParameters() ?: [])[0]->getType();
        /** @var class-string $parameterClassName */
        $parameterClassName = $parameterType->getName();
        $parameterMetadata = DefinitionMetadata::fromReflectionClass(new \ReflectionClass($parameterClassName));

        return new self(
            name: $definitionMetadata->name ?: $reflection->getShortName(),
            type: 'array',
            description: $definitionMetadata->description,
            items: $parameterMetadata->toReferenceType()
        );
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    private static function fromObjectReflectionClass(\ReflectionClass $reflection): self
    {
        $definitionMetadata = DefinitionMetadata::fromReflectionClass($reflection);

        $properties = [];
        $required = [];
        foreach ($reflection->getConstructor()?->getParameters() ?: [] as $reflectionParameter) {
            $properties[$reflectionParameter->name] = SchemaType::fromReflectionParameter($reflectionParameter);
            if (!$reflectionParameter->isDefaultValueAvailable()) {
                $required[] = $reflectionParameter->name;
            }
        }

        return new self(
            name: $definitionMetadata->name ?: $reflection->getShortName(),
            type: 'object',
            description: $definitionMetadata->description,
            properties: $properties,
            required: $required
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this));
    }
}
