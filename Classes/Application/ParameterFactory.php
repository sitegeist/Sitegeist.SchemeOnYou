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
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaDenormalizer;

#[Flow\Scope('singleton')]
class ParameterFactory
{
    public function __construct(
        private readonly SchemaDenormalizer $denormalizer
    ) {
    }

    /**
     * @param class-string $className
     * @return array<string,object|bool|int|string|float|null>
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

            $parameters[$parameter->name] = $this->denormalizer->denormalizeValue($parameterValueFromRequest, $parameterTypeName);
        }

        return $parameters;
    }
}
