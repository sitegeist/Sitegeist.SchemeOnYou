<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class IsCollection
{
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
        if ($parameters[0]->isVariadic() === false) {
            return false;
        }
        if ($reflection->isReadOnly() === false) {
            return false;
        }
        return true;
    }
}
