<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class IsSingleValueDataTransferObject
{
    /**
     * @var array<class-string,bool>
     */
    private static array $evaluationRuntimeCache = [];

    /**
     * @param class-string $className
     */
    public static function isSatisfiedByClassName(string $className): bool
    {
        if (!array_key_exists($className, self::$evaluationRuntimeCache)) {
            self::$evaluationRuntimeCache[$className] = self::evaluateReflectionClass(new \ReflectionClass($className));
        }

        return self::$evaluationRuntimeCache[$className];
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    public static function isSatisfiedByReflectionClass(\ReflectionClass $reflectionClass): bool
    {
        if (!array_key_exists($reflectionClass->name, self::$evaluationRuntimeCache)) {
            self::$evaluationRuntimeCache[$reflectionClass->name] = self::evaluateReflectionClass($reflectionClass);
        }

        return self::$evaluationRuntimeCache[$reflectionClass->name];
    }

    /**
     * @param \ReflectionClass<object> $reflectionClass
     */
    private static function evaluateReflectionClass(\ReflectionClass $reflectionClass): bool
    {
        if ($reflectionClass->isEnum()) {
            /** @phpstan-ignore-next-line */
            return (new \ReflectionEnum($reflectionClass->getName()))->isBacked();
        }
        if (IsDataTransferObject::isSatisfiedByReflectionClass($reflectionClass)) {
            $parameters = $reflectionClass->getConstructor()?->getParameters() ?: [];
            if (count($parameters) === 1 && $parameters[0]->name === 'value') {
                return true;
            }
        }
        return false;
    }
}
