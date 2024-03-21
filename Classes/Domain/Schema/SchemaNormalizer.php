<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

class SchemaNormalizer
{
    use IsTrait;

    public static function normalizeValue(null|int|bool|string|float|object $value): array|int|bool|string|float|null
    {
        return self::convertValue($value);
    }

    private static function convertValue(null|int|bool|string|float|object $value): array|int|bool|string|float|null
    {
        if ($value === null) {
            return null;
        } elseif (is_scalar($value)) {
            return $value;
        } elseif (is_object($value)) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format(\DateTimeInterface::RFC3339);
            } elseif ($value instanceof \DateInterval) {
                return self::convertDateInterval($value);
            } elseif ($value instanceof \BackedEnum) {
                return $value->value;
            } elseif (self::isCollectionClassName(get_class($value))) {
                return self::convertCollection($value);
            } elseif (self::isValueObjectClassName(get_class($value))) {
                return self::convertValueObject($value);
            }
        }

        throw new \DomainException('Unsupported type. Only scalar types, BackedEnums, Collections, ValueObjects are supported');
    }


    /**
     * @param object $value
     * @return array<integer,int,bool,float,string,array>
     */
    private static function convertCollection(object $value): array
    {
        $values = array_values(get_object_vars($value));
        if (count($values) === 1 && is_array($values[0])) {
            return array_map(
                fn($subvalue) => self::convertValue($subvalue),
                $values[0]
            );
        }
        var_dump($values);
        throw new \DomainException('Collections must have a single array property');
    }

    /**
     * @param object $value
     * @return array<string,int,bool,float,string,array>
     */
    private static function convertValueObject(object $value): array
    {
        return array_map(
            fn($subvalue) => self::convertValue($subvalue),
            get_object_vars($value)
        );
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
