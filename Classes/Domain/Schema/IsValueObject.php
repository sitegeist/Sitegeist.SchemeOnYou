<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class IsValueObject
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
        if ($reflection instanceof \ReflectionEnum) {
            return true;
        }
        if ($reflection->isReadOnly() === false) {
            return false;
        }

        $parameters = $reflection->getConstructor()?->getParameters() ?: [];
        foreach ($parameters as $parameter) {
            $parameterType = $parameter->getType();
            if ($parameterType instanceof \ReflectionType && $parameter->isPromoted() && IsSupported::isSatisfiedByReflectionType($parameterType)) {
                continue;
            }
            return false;
        }
        return true;
    }
}
