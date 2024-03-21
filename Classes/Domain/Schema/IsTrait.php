<?php

namespace Sitegeist\SchemeOnYou\Domain\Schema;

trait IsTrait
{
    private static array $isCollectionResults = [];
    private static array $isValueObjectResults = [];

    protected static function isCollectionClassName(string $className): bool
    {
        if (array_key_exists($className, self::$isCollectionResults)) {
            return self::$isCollectionResults[$className];
        }
        self::$isCollectionResults[$className] = IsCollection::isSatisfiedByClassName($className);
        return self::$isCollectionResults[$className];
    }

    protected static function isValueObjectClassName(string $className): bool
    {
        if (array_key_exists($className, self::$isValueObjectResults)) {
            return self::$isValueObjectResults[$className];
        }
        self::$isValueObjectResults[$className] = IsValueObject::isSatisfiedByClassName($className);
        return self::$isValueObjectResults[$className];
    }
}
