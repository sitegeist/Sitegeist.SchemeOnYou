<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Reflection\ClassReflection;

class SchemaDenormalizer
{
    /**
     * @param int|bool|string|float|array<mixed>|null $value
     * @return object|int|bool|string|float|null
     */
    public static function denormalizeValue(null|int|bool|string|float|array $value, string $targetType): object|int|bool|string|float|null
    {
        return self::convertValue($value, $targetType);
    }

    /**
     * @param null|int|bool|string|float|array<mixed> $value
     * @return object|int|bool|string|float|null
     */
    private static function convertValue(null|int|bool|string|float|array $value, string $targetType): object|int|bool|string|float|null
    {
        if ($value === null) {
            return null;
        } elseif ($targetType === 'string') {
            return match (is_string($value)) {
                true => $value,
                false => throw new \DomainException('Strings must be sent as such')
            };
        } elseif ($targetType === 'int') {
            return (int) $value;
        } elseif ($targetType === 'float') {
            return (float) $value;
        } elseif ($targetType === 'bool') {
            return (bool) $value;
        } elseif ($targetType === \DateTime::class) {
            return self::convertDateTime($value);
        } elseif ($targetType === \DateTimeImmutable::class) {
            return self::convertDateTimeImmutable($value);
        } elseif ($targetType === \DateInterval::class) {
            return self::convertDateInterval($value);
        } elseif (
            // Enums are final, so is_a suffices
            is_a($targetType, \BackedEnum::class, true)
        ) {
            return match (true) {
                is_int($value) || is_string($value) => $targetType::from($value),
                default => throw new \DomainException('Can only denormalize enums from int or string')
            };
        } elseif (is_array($value) && class_exists($targetType) && IsDataTransferObjectCollection::isSatisfiedByClassName($targetType)) {
            return self::convertCollection($value, $targetType);
        } elseif (class_exists($targetType) && IsDataTransferObject::isSatisfiedByClassName($targetType)) {
            return self::convertValueObject($value, $targetType);
        }

        throw new \DomainException('Unsupported type. Only scalar types, BackedEnums, Collections, ValueObjects are supported');
    }

    /**
     * @param array<mixed> $value
     */
    private static function convertCollection(array $value, string $targetType): object
    {
        $reflection = new ClassReflection($targetType);
        $parameterReflection = $reflection->getConstructor()->getParameters()[0];
        $parameterType = $parameterReflection->getType();
        if (!$parameterType instanceof \ReflectionNamedType) {
            throw new \DomainException('Only named parameters are supported');
        }
        return new $targetType(
            ...array_map(
                fn($item) => self::convertValue($item, $parameterType->getName()),
                $value
            )
        );
    }

    /**
     * @param array<string,mixed>|int|float|string|bool $value
     */
    private static function convertValueObject(array|int|float|string|bool $value, string $targetType): object
    {
        $reflection = new ClassReflection($targetType);
        $parameterReflections = $reflection->getConstructor()->getParameters();
        $convertedArguments = [];
        if (is_array($value)) {
            foreach ($parameterReflections as $name => $parameter) {
                $type = $parameter->getType();
                if ($parameter->isDefaultValueAvailable() && !array_key_exists($parameter->getName(), $value)) {
                    continue;
                }
                $convertedArguments[$name] = match (true) {
                    $type === null => throw new \DomainException('Cannot convert untyped property ' . $parameter->getName()),
                    $type instanceof \ReflectionNamedType => self::convertValue($value[$parameter->getName()], $type->getName()),
                    default => throw new \DomainException('Cannot convert ' . get_class($type) . ' yet'),
                };
            }
            return new $targetType(...$convertedArguments);
        } elseif (count($parameterReflections) === 1 && $parameterReflections[0]->getName() === 'value' && $parameterReflections[0]->getType() instanceof \ReflectionNamedType) {
            $convertedValue = self::convertValue($value, $parameterReflections[0]->getType()->getName());
            return new $targetType(value: $convertedValue);
        }
        throw new \DomainException('Only single value objects can be serialized as single value');
    }

    /**
     * @param array<string,mixed>|int|float|string|bool $value
     */
    protected static function convertDateTime(array|float|bool|int|string $value): \DateTime
    {
        $converted = match (true) {
            is_string($value) => \DateTime::createFromFormat(\DateTimeInterface::RFC3339, $value),
            default => false,
        };
        if ($converted === false) {
            throw new \DomainException('Can only denormalize \DateTime from an RFC 3339 string');
        }
        return $converted;
    }

    /**
     * @param array<string,mixed>|int|float|string|bool $value
     */
    protected static function convertDateTimeImmutable(array|float|bool|int|string $value): \DateTimeImmutable
    {
        $converted = match (true) {
            is_string($value) => \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339, $value),
            default => false,
        };
        if ($converted === false) {
            throw new \DomainException('Can only denormalize \DateTimeImmutable from an RFC 3339 string');
        }
        return $converted;
    }

    /**
     * @param array<string,mixed>|int|float|string|bool $value
     */
    protected static function convertDateInterval(array|float|bool|int|string $value): \DateInterval
    {
        $converted = match (true) {
            is_string($value) => new \DateInterval($value),
            default => false,
        };
        if ($converted === false) {
            throw new \DomainException('Can only denormalize \DateInterval from an ISO 8601 string');
        }
        return $converted;
    }
}
