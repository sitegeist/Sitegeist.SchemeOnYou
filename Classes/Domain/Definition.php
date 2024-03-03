<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class Definition implements \JsonSerializable
{
    /**
     * @param array<int,int|string>|null $enum
     */
    public function __construct(
        public string $type,
        public string $description,
        public ?array $enum = null
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
        return match ($reflection->getBackingType()?->getName()) {
            'string' => new self(
                'string',
                ReflectionDescriptionCollection::fromReflection($reflection)->render(),
                array_map(
                    fn (\ReflectionEnumBackedCase $case): string => $case->getBackingValue(),
                    $reflection->getCases()
                )
            ),
            'int' => new self(
                'int',
                ReflectionDescriptionCollection::fromReflection($reflection)->render(),
                array_map(
                    fn (\ReflectionEnumBackedCase $case): int => $case->getBackingValue(),
                    $reflection->getCases()
                )
            ),
            default => throw new \InvalidArgumentException(
                'Cannot create definition from non-backed enum ' . $reflection->name, 1709499876
            ),
        };
    }

    public static function fromReflectionClass(\ReflectionClass $reflection): self
    {
        if (!in_array('JsonSerializable', $reflection->getInterfaceNames())) {
            throw new \DomainException(
                'Given class ' . $reflection->name . ' does not implement the required \JsonSerializable interface',
                1709503193
            );
        }

        $jsonSerializeReturnType = $reflection->getMethod('jsonSerialize')->getReturnType();
        $returnType = $jsonSerializeReturnType instanceof \ReflectionNamedType ? $jsonSerializeReturnType->getName() : null;
        $allowedReturnTypes = ['string', 'int', 'float', 'array'];
        if (!in_array($returnType, $allowedReturnTypes)) {
            throw new \DomainException(
                'Given class ' . $reflection->name . ' has invalid return type for jsonSerializable, must be one of "'
                . implode('","', $allowedReturnTypes) . '"',
                1709503874
            );
        }

        return new self(
            match ($returnType) {
                'string' => 'string',
                'int' => 'int',
                'float' => 'number',
                'array' => 'object'
            },
            ReflectionDescriptionCollection::fromReflection($reflection)->render(),
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
