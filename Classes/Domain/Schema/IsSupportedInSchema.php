<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class IsSupportedInSchema
{
    public static function isSatisfiedByTypeName(string $name): bool
    {
        return self::isSatisfiedByClassName($name) || self::isSatisfiedByInterfaceName($name);
    }

    public static function isSatisfiedByClassName(string $className): bool
    {
        if (class_exists($className)) {
            return self::isSatisfiedByReflectionClass(new \ReflectionClass($className));
        }
        return false;
    }

    public static function isSatisfiedByInterfaceName(string $interfaceName): bool
    {
        if (interface_exists($interfaceName, true)) {
            // we accept all interfaces ... for now
            return true;
        }
        return false;
    }

    public static function isSatisfiedByReflectionType(\ReflectionType $reflection): bool
    {
        if ($reflection instanceof \ReflectionNamedType) {
            if (in_array($reflection->getName(), ['string', 'bool', 'int', 'float'])) {
                return true;
            }
            return self::isSatisfiedByTypeName($reflection->getName());
        } elseif ($reflection instanceof \ReflectionUnionType) {
            foreach ($reflection->getTypes() as $type) {
                if ($type instanceof \ReflectionNamedType) {
                    if (self::isSatisfiedByReflectionType($type) === false) {
                        return false; // every part of a union has to be a named type that matched the conditions
                    }
                } else {
                    return false; // only named types in unions are allowed
                }
            }
            return true;
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
