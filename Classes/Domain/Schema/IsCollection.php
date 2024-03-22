<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class IsCollection
{
    /**
     * @param class-string $className
     */
    public static function isSatisfiedByClassName(string $className): bool
    {
        $reflection = new \ReflectionClass($className);
        return self::isSatisfiedByReflectionClass($reflection);
    }

    /**
     * @param \ReflectionClass<object> $reflection
     */
    public static function isSatisfiedByReflectionClass(\ReflectionClass $reflection): bool
    {
        $parameters = $reflection->getConstructor()?->getParameters() ?: [];
        if (count($parameters) !== 1) {
            return false;
        }
        if ($reflection->isReadOnly() === false) {
            return false;
        }
        $collectionParameter = $parameters[0];
        if ($collectionParameter->isVariadic() === false) {
            return false;
        }
        $collectionParameterType = $collectionParameter->getType();
        if ($collectionParameterType instanceof \ReflectionNamedType) {
            if (IsSupported::isSatisfiedByReflectionType($collectionParameterType)) {
                return true;
            }
        }

        return false;
    }
}
