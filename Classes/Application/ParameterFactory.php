<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Application;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\ObjectManagement\Proxy\ProxyInterface;
use Sitegeist\SchemeOnYou\Domain\Metadata\Parameter as ParameterAttribute;
use Sitegeist\SchemeOnYou\Domain\Metadata\RequestBody;
use Sitegeist\SchemeOnYou\Domain\Metadata\RequestBodyContentType;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterLocation;
use Sitegeist\SchemeOnYou\Domain\Path\RequestParameterContract;

#[Flow\Proxy(false)]
final readonly class ParameterFactory
{
    /**
     * @param class-string $className
     * @return array<string,RequestParameterContract|bool|int|string|float>
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
            if ($type->allowsNull()) {
                throw new \DomainException('Nullable types are not supported yet', 1709721755);
            }
            $parameterTypeName = $type->getName();
            $parameterValueFromRequest = match (ParameterAttribute::tryFromReflectionParameter($parameter)?->in) {
                ParameterLocation::LOCATION_PATH => $request->getArgument($parameter->name),
                ParameterLocation::LOCATION_QUERY => $request->getHttpRequest()->getQueryParams()[$parameter->name],
                ParameterLocation::LOCATION_HEADER => $request->getHttpRequest()->getHeader($parameter->name),
                ParameterLocation::LOCATION_COOKIE
                => $request->getHttpRequest()->getCookieParams()[$parameter->name],
                null => match (RequestBody::fromReflectionParameter($parameter)->contentType) {
                    RequestBodyContentType::CONTENT_TYPE_JSON => \json_decode(
                        (string)$request->getHttpRequest()->getBody(),
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    ),
                    RequestBodyContentType::CONTENT_TYPE_FORM => $request->getArgument($parameter->name)
                }
            };

            if (class_exists($parameterTypeName)) {
                $parameterReflectionClass = new \ReflectionClass($parameterTypeName);
                if (!$parameterReflectionClass->implementsInterface(RequestParameterContract::class)) {
                    throw new \DomainException(
                        'Can only resolve parameters of type ' . RequestParameterContract::class,
                        1709722058
                    );
                }
                /** @var class-string<RequestParameterContract> $parameterTypeName */
                $parameters[$parameter->name] = $parameterTypeName::fromRequestParameter($parameterValueFromRequest);
            } else {
                $parameters[$parameter->name] = match ($parameterTypeName) {
                    'string' => (string) $parameterValueFromRequest,
                    'int' => (int) $parameterValueFromRequest,
                    'float' => (float) $parameterValueFromRequest,
                    'bool' => (bool) $parameterValueFromRequest,
                    default => throw new \DomainException(sprintf('Cannot resolve parameters of type %s', $parameterTypeName), 1709721783)
                };
            }
        }

        return $parameters;
    }
}
