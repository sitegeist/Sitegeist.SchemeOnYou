<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class IsSupported
{
    public static function isSatisfiedByClassName(string $className): bool
    {
        if (class_exists($className) || interface_exists($className)) {
            $reflection = new \ReflectionClass($className);
            return self::isSatisfiedByReflectionClass($reflection);
        }
        return false;
    }

    public static function isSatisfiedByReflectionType(\ReflectionType $reflection): bool
    {
        if ($reflection instanceof \ReflectionNamedType) {
            if (in_array($reflection->getName(), ['string', 'bool', 'int', 'float'])) {
                return true;
            }
            return self::isSatisfiedByClassName($reflection->getName());
        }
        return false;
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    public static function isSatisfiedByReflectionClass(\ReflectionClass $reflection): bool
    {
        if (in_array($reflection->getName(), [\DateInterval::class, \DateTimeImmutable::class, \DateTime::class])) {
            return true;
        } elseif (is_a($reflection->getName(), \BackedEnum::class, true)) {
            return true;
        } elseif (IsDataTransferObject::isSatisfiedByReflectionClass($reflection)) {
            return true;
        } elseif (IsDataTransferObjectCollection::isSatisfiedByReflectionClass($reflection)) {
            return true;
        }
        return false;
    }
}
