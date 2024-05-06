<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Sitegeist\SchemeOnYou\Domain\Metadata\StringProperty;

class SchemaNormalizer
{
    /**
     * @return array<mixed>|int|bool|string|float|null
     */
    public static function normalizeValue(null|int|bool|string|float|object $value): array|int|bool|string|float|null
    {
        return self::convertValue($value);
    }

    /**
     * @return array<mixed>|int|bool|string|float|null
     */
    private static function convertValue(null|int|bool|string|float|object $value, ?\ReflectionParameter $reflectionParameter = null): array|int|bool|string|float|null
    {
        if ($value === null) {
            return null;
        } elseif (is_scalar($value)) {
            return $value;
        } elseif (is_object($value)) {
            if ($value instanceof \DateTimeInterface) {
                $propertyAttribute = $reflectionParameter ? StringProperty::tryFromReflectionParameter($reflectionParameter) : null;
                $format = match ($propertyAttribute?->format) {
                    StringProperty::FORMAT_DATE => 'Y-m-d',
                    default => \DateTimeInterface::RFC3339
                };
                return $value->format($format);
            } elseif ($value instanceof \DateInterval) {
                return self::convertDateInterval($value);
            } elseif ($value instanceof \BackedEnum) {
                return $value->value;
            } elseif (IsDataTransferObjectCollection::isSatisfiedByReflectionClass(new \ReflectionClass($value))) {
                return self::convertCollection($value, new \ReflectionClass($value));
            } elseif (IsDataTransferObject::isSatisfiedByReflectionClass(new \ReflectionClass($value))) {
                return self::convertValueObject($value, new \ReflectionClass($value));
            }
            throw new \DomainException('Unsupported object ' . get_class($value));
        }
        /** @phpstan-ignore-next-line not too sure we always terminate before here */
        throw new \DomainException('Unsupported type. Only scalar types, BackedEnums, Collections, ValueObjects are supported');
    }


    /**
     * @param \ReflectionClass<object> $reflectionClass
     * @return array<integer,int|bool|float|string|array<mixed>|null>
     */
    private static function convertCollection(object $value, \ReflectionClass $reflectionClass): array
    {
        $values = array_values(get_object_vars($value));
        if (count($values) === 1 && is_array($values[0])) {
            $reflectionParameter = $reflectionClass->getConstructor()?->getParameters()[0] ?? null;
            return array_map(
                fn($subvalue) => self::convertValue($subvalue, $reflectionParameter),
                $values[0]
            );
        }
        throw new \DomainException('Collections must have a single array property');
    }

    /**
     * @return array<string,int|bool|float|string|array<mixed>|null>|int|float|string
     * @param \ReflectionClass<object> $reflectionClass
     */
    private static function convertValueObject(object $value, \ReflectionClass $reflectionClass): array|int|float|string
    {
        $properties = get_object_vars($value);
        if (array_keys($properties) === ['value']) {
            $reflectionParameter = $reflectionClass->getConstructor()?->getParameters()[0] ?? null;
            if ($reflectionParameter) {
                $reflectionType = $reflectionParameter->getType();
                if (
                    $reflectionType instanceof \ReflectionNamedType
                    && ($reflectionType->getName() === \DateTimeImmutable::class || $reflectionType->getName() === \DateTime::class)
                    && $properties['value'] instanceof \DateTimeInterface
                ) {
                    $stringPropertyAttribute = StringProperty::tryFromReflectionParameter($reflectionParameter);
                    if ($stringPropertyAttribute) {
                        $format = match ($stringPropertyAttribute->format) {
                            StringProperty::FORMAT_DATE => 'Y-m-d',
                            default => \DateTimeImmutable::RFC3339
                        };
                        return $properties['value']->format($format);
                    }
                }
            }
            return $properties['value'];
        }

        $propertyKey = 0;
        $conversion = [];
        foreach (get_object_vars($value) as $propertyName => $propertyValue) {
            $conversion[$propertyName] = self::convertValue(
                $propertyValue,
                $reflectionClass->getConstructor()?->getParameters()[$propertyKey]
            );
            $propertyKey++;
        }

        return $conversion;
    }

    /**
     * @see https://www.php.net/manual/en/dateinterval.construct.php#119260
     */
    private static function convertDateInterval(\DateInterval $value): string
    {
        $date = null;
        if ($value->y) {
            $date .= $value->y . 'Y';
        }
        if ($value->m) {
            $date .= $value->m . 'M';
        }
        if ($value->d) {
            $date .= $value->d . 'D';
        }

        $time = null;
        if ($value->h) {
            $time .= $value->h . 'H';
        }
        if ($value->i) {
            $time .= $value->i . 'M';
        }
        if ($value->s) {
            $time .= $value->s . 'S';
        }
        if ($time) {
            $time = 'T' . $time;
        }

        $text = 'P' . $date . $time;
        if ($text === 'P') {
            return 'PT0S';
        }
        return $text;
    }
}
