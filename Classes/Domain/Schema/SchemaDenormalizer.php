<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ClassReflection;

#[Flow\Scope('singleton')]
class SchemaDenormalizer
{
    use IsTrait;

    /**
     * @param int|bool|string|float|array<mixed>|null $value
     * @return object|array<mixed>|int|bool|string|float|null
     */
    public function denormalizeValue(null|int|bool|string|float|array $value, string $targetType): object|array|int|bool|string|float|null
    {
        return $this->convertValue($value, $targetType);
    }

    /**
     * @param null|int|bool|string|float|array<mixed> $value
     * @return object|array<mixed>|int|bool|string|float|null
     */
    private function convertValue(null|int|bool|string|float|array $value, string $targetType): object|array|int|bool|string|float|null
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
            return match (true) {
                is_string($value) => \DateTime::createFromFormat(\DateTimeInterface::RFC3339, $value),
                default => throw new \DomainException('Can only denormalize \DateTime from an RFC 3339 string')
            };
        } elseif ($targetType === \DateTimeImmutable::class) {
            return match (true) {
                is_string($value) => \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339, $value),
                default => throw new \DomainException('Can only denormalize \DateTimeImmutable from an RFC 3339 string')
            };
        } elseif ($targetType === \DateInterval::class) {
            return match (true) {
                is_string($value) => new \DateInterval($value),
                default => throw new \DomainException('Can only denormalize \DateInterval from string')
            };
        } elseif (
            // Enums are final, so is_a suffices
            is_a($targetType, \BackedEnum::class, true)
        ) {
            return match (true) {
                is_int($value) || is_string($value) => $targetType::from($value),
                default => throw new \DomainException('Can only denormalize enums from int or string')
            };
        } elseif (is_array($value) && class_exists($targetType) && $this->isCollectionClassName($targetType)) {
            return $this->convertCollection($value, $targetType);
        } elseif (is_array($value) && class_exists($targetType) && $this->isValueObjectClassName($targetType)) {
            return $this->convertValueObject($value, $targetType);
        }

        throw new \DomainException('Unsupported type. Only scalar types, BackedEnums, Collections, ValueObjects are supported');
    }

    /**
     * @param array<mixed> $value
     */
    private function convertCollection(array $value, string $targetType): object
    {
        $reflection = new ClassReflection($targetType);
        $parameterReflection = $reflection->getConstructor()->getParameters()[0];
        $parameterType = $parameterReflection->getType();
        if (!$parameterType instanceof \ReflectionNamedType) {
            throw new \DomainException('Only named parameters are supported');
        }
        return new $targetType(
            ...array_map(
                fn($item) => $this->convertValue($item, $parameterType->getName()),
                $value
            )
        );
    }

    /**
     * @param array<string,mixed> $value
     */
    private function convertValueObject(array $value, string $targetType): object
    {
        $reflection = new ClassReflection($targetType);
        $parameterReflections = $reflection->getConstructor()->getParameters();
        $convertedArguments = [];
        foreach ($parameterReflections as $name => $parameter) {
            $type = $parameter->getType();
            $convertedArguments[$name] = match (true) {
                $type === null => throw new \DomainException('Cannot convert untyped property ' . $parameter->getName()),
                $type instanceof \ReflectionNamedType => $this->convertValue($value[$parameter->getName()], $type->getName()),
                default => throw new \DomainException('Cannot convert ' . get_class($type) . ' yet'),
            };
        }

        return new $targetType(...$convertedArguments);
    }
}
