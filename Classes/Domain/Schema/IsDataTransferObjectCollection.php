<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class IsDataTransferObjectCollection
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
        // readonly
        if ($reflectionClass->isReadOnly() === false) {
            return false;
        }
        // a single variadic constructor parameter of a supported type
        $parameters = $reflectionClass->getConstructor()?->getParameters() ?: [];
        if (count($parameters) !== 1) {
            return false;
        }
        $collectionParameter = $parameters[0];
        if ($collectionParameter->isVariadic() === false) {
            return false;
        }
        $collectionParameterType = $collectionParameter->getType();
        if ($collectionParameterType instanceof \ReflectionType) {
            if (!IsSupportedInSchema::isSatisfiedByReflectionType($collectionParameterType)) {
                return false;
            }
        }
        // a single property of type array
        $properties = $reflectionClass->getProperties();
        if (count($properties) === 1 && $properties[0]->getType() instanceof \ReflectionNamedType && $properties[0]->getType()->getName() === 'array') {
            return true;
        }
        return false;
    }
}
