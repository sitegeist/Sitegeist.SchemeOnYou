<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class IsDataTransferObject
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
        if ($reflectionClass instanceof \ReflectionEnum) {
            return true;
        }
        if ($reflectionClass->isReadOnly() === false) {
            return false;
        }

        $parameters = $reflectionClass->getConstructor()?->getParameters() ?: [];
        foreach ($parameters as $reflectionParameter) {
            $parameterType = $reflectionParameter->getType();
            if ($parameterType instanceof \ReflectionType && $reflectionParameter->isPromoted() && IsSupportedInSchema::isSatisfiedByReflectionType($parameterType)) {
                continue;
            }
            return false;
        }

        return true;
    }
}
