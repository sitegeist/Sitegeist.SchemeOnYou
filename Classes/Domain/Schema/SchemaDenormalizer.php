<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Reflection\ClassReflection;

class SchemaDenormalizer
{
    public static function denormalizeValue(null|int|bool|string|float|array $value, string $targetType): object|array|int|bool|string|float|null
    {
        return self::convertValue($value, $targetType);
    }

    private static function convertValue(null|int|bool|string|float|array $value, string $targetType): object|array|int|bool|string|float|null
    {
        if ($value === null) {
            return null;
        } elseif ($targetType === 'string') {
            return (string) $value;
        } elseif ($targetType === 'int') {
            return (int) $value;
        } elseif ($targetType === 'float') {
            return (float) $value;
        } elseif ($targetType === 'bool') {
            return (bool) $value;
        } elseif ($targetType === \DateTime::class) {
            return new \DateTime($value);
        } elseif ($targetType === \DateTimeImmutable::class) {
            return new \DateTimeImmutable($value);
        } elseif ($targetType === \DateInterval::class) {
            return new \DateInterval($value);
        } elseif (is_a($targetType, \BackedEnum::class, true)) {
            return $targetType::from($value);
        } elseif (is_array($value) && IsCollection::isSatisfiedByClassName($targetType)) {
            return self::convertCollection($value, $targetType);
        } elseif (is_array($value) && IsValueObject::isSatisfiedByClassName($targetType)) {
            return  self::convertValueObject($value, $targetType);
        }

        throw new \DomainException('Unsupported type. Only scalar types, BackedEnums, Collections, ValueObjects are supported');
    }

    private static function convertCollection(array $value, string $targetType): object
    {
        $reflection = new ClassReflection($targetType);
        $parameterReflection = $reflection->getConstructor()?->getParameters()[0];
        $parameterType = $parameterReflection->getType();
        if (!$parameterType instanceof \ReflectionNamedType) {
            throw new \DomainException('Only named paramerters are supported');
        }
        return new $targetType(
            ...array_map(
                fn($item) => self::convertValue($item, $parameterType->getName()),
                $value
            )
        );
    }

    private static function convertValueObject(array $value, string $targetType): object
    {
        $reflection = new ClassReflection($targetType);
        $parameterReflections = $reflection->getConstructor()?->getParameters();
        $convertedArguments = array_map(
            fn($parameter) => self::convertValue($value[$parameter->getName()], $parameter->getType()->getName()),
            $parameterReflections
        );

        return new $targetType(...$convertedArguments);
    }
}
