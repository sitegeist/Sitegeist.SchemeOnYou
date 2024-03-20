<?php
declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

class ResponseSerializer
{
    public static function serializeResponse(int|bool|string|float|object $responseObject):string
    {
        return json_encode(self::convertValue($responseObject), JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    private static function convertValue(null|int|bool|string|float|object $value): array|int|bool|string|float
    {
        if (is_object($value)) {
            if ($value instanceof \DateTimeInterface) {
                return $value->format(\DateTimeInterface::RFC3339);
            } elseif ($value instanceof \DateInterval) {
                return $value->format(\DateTimeInterface::ISO8601_EXPANDED);
            } elseif (IsCollection::isSatisfiedByClassName(get_class($value))) {
                return self::convertCollection($value);
            } elseif (IsValueObject::isSatisfiedByClassName(get_class($value))) {
                return self::convertValueObject($value);
            } else {
                throw new \DomainException('Unsupported type only scalar types, Collections and ValueObjects are supported');
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
        if (count($values) == 1) {
            return array_map(
                fn($subvalue) => self::convertValue($subvalue),
                $values[0]
            );
        }
        throw new \DomainException('Collections must not have additional properties');
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
}
