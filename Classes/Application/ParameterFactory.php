<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Application;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Sitegeist\SchemeOnYou\Domain\Metadata\Parameter as ParameterAttribute;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterLocation;
use Sitegeist\SchemeOnYou\Domain\Path\RequestParameterContract;

#[Flow\Proxy(false)]
final readonly class ParameterFactory
{
    /**
     * @param class-string $className
     * @return array<string,RequestParameterContract>
     */
    public static function resolveParameters(string $className, string $methodName, ActionRequest $request): array
    {
        $parameters = [];
        $reflectionMethod = new \ReflectionMethod($className, $methodName);
        foreach ($reflectionMethod->getParameters() as $parameter) {
            $type = $parameter->getType();
            if (!$type instanceof \ReflectionNamedType) {
                throw new \DomainException('Can only resolve named parameters with single type', 1709721743);
            }
            $parameterClassName = $type->getName();
            if (!class_exists($parameterClassName)) {
                throw new \DomainException('Can only resolve parameters of type class', 1709721783);
            }
            $parameterReflectionClass = new \ReflectionClass($parameterClassName);
            if (!$parameterReflectionClass->implementsInterface(RequestParameterContract::class)) {
                throw new \DomainException(
                    'Can only resolve parameters of type ' . RequestParameterContract::class,
                    1709722058
                );
            }
            /** @var class-string<RequestParameterContract> $parameterClassName */
            $parameters[$parameter->name] = $parameterClassName::fromRequestParameter(
                match (ParameterAttribute::fromReflectionParameter($parameter)->in) {
                    ParameterLocation::LOCATION_PATH => $request->getArgument($parameter->name),
                    ParameterLocation::LOCATION_QUERY => $request->getHttpRequest()->getQueryParams()[$parameter->name],
                    ParameterLocation::LOCATION_HEADER => $request->getHttpRequest()->getHeader($parameter->name),
                    ParameterLocation::LOCATION_COOKIE
                        => $request->getHttpRequest()->getCookieParams()[$parameter->name],
                }
            );
        }

        return $parameters;
    }
}
