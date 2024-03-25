<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

trait IsTrait
{
    /**
     * @var array<class-string,bool>
     */
    private static array $isCollectionResults = [];

    /**
     * @var array<class-string,bool>
     */
    private static array $isValueObjectResults = [];

    /**
     * @param class-string $className
     */
    protected static function isCollectionClassName(string $className): bool
    {
        return self::$isCollectionResults[$className] ??= IsCollection::isSatisfiedByClassName($className);
    }

    /**
     * @param class-string $className
     */
    protected static function isValueObjectClassName(string $className): bool
    {
        return self::$isValueObjectResults[$className] ??= IsValueObject::isSatisfiedByClassName($className);
    }
}
