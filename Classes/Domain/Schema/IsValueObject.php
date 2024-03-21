<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class IsValueObject
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
        if ($reflection instanceof \ReflectionEnum) {
            return true;
        }
        if ($reflection->isReadOnly() === false) {
            return false;
        }

        $parameters = $reflection->getConstructor()?->getParameters() ?: [];
        foreach ($parameters as $parameter) {
            $parameterType = $parameter->getType();
            if ($parameterType instanceof \ReflectionNamedType) {
                if (in_array($parameterType->getName(), ['int', 'float', 'string', 'bool', \DateTime::class, \DateTimeImmutable::class, \DateInterval::class])) {
                    continue;
                }
                if (is_a($parameterType->getName(), \BackedEnum::class, true)) {
                    continue;
                }
                if (IsValueObject::isSatisfiedByClassName($parameterType->getName()) || IsCollection::isSatisfiedByClassName($parameterType->getName())) {
                    continue;
                }
            } elseif ($parameterType instanceof \ReflectionUnionType) {
                // @todo implement support for union types
                return false;
            }
            return false;
        }
        return true;
    }
}
