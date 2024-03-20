<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class IsSupported
{
    public static function isSatisfiedByClassName(string $className): bool
    {
        $reflection = new \ReflectionClass($className);
        return self::isSatisfiedByReflectionClass($reflection);
    }

    public static function isSatisfiedByReflectionType(\ReflectionType $reflection): bool
    {
        if ($reflection instanceof \ReflectionNamedType) {
            return self::isSatisfiedByClassName($reflection->getName());
        }
        return false;
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    public static function isSatisfiedByReflectionClass(\ReflectionClass $reflection): bool
    {
        if ($reflection instanceof \ReflectionEnum) {
            return true;
        }
        if (in_array($reflection->getName(), [\DateTimeInterface::class, \DateTimeImmutable::class, \DateTime::class])) {
            return true;
        }
        if (IsValueObject::isSatisfiedByReflectionClass($reflection)) {
            return true;
        }
        if (IsCollection::isSatisfiedByReflectionClass($reflection)) {
            return true;
        }
        return false;
    }
}
