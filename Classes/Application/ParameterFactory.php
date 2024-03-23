<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Application;

use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\ObjectManagement\Proxy\ProxyInterface;
use Sitegeist\SchemeOnYou\Domain\Metadata\Parameter as ParameterAttribute;
use Sitegeist\SchemeOnYou\Domain\Metadata\RequestBody;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaDenormalizer;

readonly class ParameterFactory
{
    public function __construct(
        private SchemaDenormalizer $denormalizer
    ) {
    }

    /**
     * @param class-string $className
     * @return array<string,array<mixed>|object|bool|int|string|float|null>
     */
    public function resolveParameters(string $className, string $methodName, ActionRequest $request): array
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
            if ($type->allowsNull()) {
                throw new \DomainException('Nullable types are not supported yet', 1709721755);
            }

            $parameterAttribute = ParameterAttribute::tryFromReflectionParameter($parameter);
            if ($parameterAttribute) {
                $parameterValueFromRequest = $parameterAttribute->in->resolveParameterFromRequest($request, $parameter->name);
                $parameterValueFromRequest = $parameterAttribute->style->decodeParameterValue($parameterValueFromRequest);
            } else {
                $requestBodyAttribute = RequestBody::fromReflectionParameter($parameter);
                $parameterValueFromRequest = $requestBodyAttribute->contentType->resolveParameterFromRequest($request, $parameter->name);
                $parameterValueFromRequest = $requestBodyAttribute->contentType->decodeParameterValue($parameterValueFromRequest);
            }

            $parameters[$parameter->name] = $this->denormalizer->denormalizeValue($parameterValueFromRequest, $type->getName());
        }

        return $parameters;
    }
}
