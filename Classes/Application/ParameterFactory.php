<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Application;

use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\ObjectManagement\Proxy\ProxyInterface;
use Sitegeist\SchemeOnYou\Domain\Metadata\Parameter as ParameterAttribute;
use Sitegeist\SchemeOnYou\Domain\Metadata\RequestBody;
use Sitegeist\SchemeOnYou\Domain\Path\NoSuchParameter;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaDenormalizer;

class ParameterFactory
{
    /**
     * @param class-string $className
     * @return array<string,array<mixed>|object|bool|int|string|float|null>
     */
    public static function resolveParameters(string $className, string $methodName, ActionRequest $request): array
    {
        $reflectionClass = new \ReflectionClass($className);
        if ($reflectionClass->implementsInterface(ProxyInterface::class)) {
            $parentClass = $reflectionClass->getParentClass();
            if (!$parentClass) {
                throw new \DomainException('Given class is a proxy class but has no original');
            }
            $reflectionClass = $parentClass;
        }
        $reflectionMethod = $reflectionClass->getMethod($methodName);
        $parameters = [];
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $type = $parameter->getType();
            if (!$type instanceof \ReflectionNamedType) {
                throw new \DomainException('Can only resolve named parameters with single type', 1709721743);
            }

            $requestBodyAttribute = RequestBody::tryFromReflectionParameter($parameter);
            if ($requestBodyAttribute) {
                $parameterValueFromRequest = $requestBodyAttribute->contentType->resolveParameterFromRequest($request, $parameter->name);
                if (!$parameterValueFromRequest instanceof NoSuchParameter) {
                    $parameterValueFromRequest = $requestBodyAttribute->contentType->decodeParameterValue($parameterValueFromRequest);
                }
            } else {
                $parameterAttribute = ParameterAttribute::fromReflectionParameter($parameter);
                $parameterValueFromRequest = $parameterAttribute->in->resolveParameterFromRequest($request, $parameter->name);
                if (!$parameterValueFromRequest instanceof NoSuchParameter) {
                    $parameterValueFromRequest = $parameterAttribute->style->decodeParameterValue($parameterValueFromRequest);
                }
            }
            if (!$parameterValueFromRequest instanceof NoSuchParameter) {
                $parameters[$parameter->name] = SchemaDenormalizer::denormalizeValue($parameterValueFromRequest, $type->getName(), $parameter);
            }
        }
        return $parameters;
    }
}
