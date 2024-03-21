<?php
declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

class SchemaSerializer
{
    public static function serializeValue(null|int|bool|string|float|object $value): string
    {
        return json_encode(self::convertValue($value), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    private static function convertValue(null|int|bool|string|float|object $value): array|int|bool|string|float|null
    {
        if (is_object($value)) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format(\DateTimeInterface::RFC3339);
            } elseif ($value instanceof \DateInterval) {
                return self::convertDateInterval($value);
            } elseif ($value instanceof \BackedEnum) {
                return $value->value;
            } elseif (IsCollection::isSatisfiedByClassName(get_class($value))) {
                return self::convertCollection($value);
            } elseif (IsValueObject::isSatisfiedByClassName(get_class($value))) {
                return self::convertValueObject($value);
            } else {
                throw new \DomainException('Unsupported type. Only scalar types, BackedEnums, Collections, ValueObjects are supported');
            }
        }

        // scalar values need no transformation
        return $value;
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
        $date = NULL;
        if ($value->y) $date .= $value->y . 'Y';
        if ($value->m) $date .= $value->m . 'M';
        if ($value->d) $date .= $value->d . 'D';

        $time = NULL;
        if ($value->h) $time .= $value->h . 'H';
        if ($value->i) $time .= $value->i . 'M';
        if ($value->s) $time .= $value->s . 'S';
        if ($time) $time = 'T' . $time;

        $text ='P' . $date . $time;
        if ($text === 'P') return 'PT0S';
        return $text;
    }
}
