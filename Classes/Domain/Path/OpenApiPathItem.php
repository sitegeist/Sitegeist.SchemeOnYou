<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\ObjectManagement\Proxy\ProxyInterface;
use Sitegeist\SchemeOnYou\Domain\Metadata\HttpMethod;
use Sitegeist\SchemeOnYou\Domain\Metadata\Parameter;
use Sitegeist\SchemeOnYou\Domain\Metadata\Path as PathMetadata;
use Sitegeist\SchemeOnYou\Domain\Metadata\RequestBody;

/**
 * @see https://swagger.io/specification/#path-item-object
 */
#[Flow\Proxy(false)]
final readonly class OpenApiPathItem implements \JsonSerializable
{
    public function __construct(
        public PathDefinition $pathDefinition,
        public HttpMethod $httpMethod,
        public OpenApiParameterCollection $parameters,
        public ?OpenApiRequestBody $requestBody,
        public OpenApiResponses $responses,
    ) {
    }

    /**
     * @param class-string $className
     */
    public static function fromMethodName(string $className, string $methodName): self
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

        return self::fromReflectionMethod($reflectionMethod);
    }

    public static function fromReflectionMethod(\ReflectionMethod $reflectionMethod): self
    {
        $pathMetadata = PathMetadata::fromReflectionMethod($reflectionMethod);

        $requestBody = null;
        $parameters = [];
        foreach ($reflectionMethod->getParameters() as $reflectionParameter) {
            $parameterProcessed = false;
            foreach ($reflectionParameter->getAttributes() as $attribute) {
                if ($attribute->getName() === RequestBody::class) {
                    if ($parameterProcessed) {
                        throw new \DomainException(
                            'Method parameter ' . $reflectionMethod->getDeclaringClass()->name
                            . '::' . $reflectionMethod->getName() . '::' . $reflectionParameter->name
                            . ' must be attributed as either OpenAPI Parameter or RequestBody and was already attributed'
                        );
                    }
                    if ($requestBody !== null) {
                        throw new \DomainException(
                            'Only one parameter can be resolved via request body',
                            1710069260
                        );
                    }
                    $requestBody = OpenApiRequestBody::fromReflectionParameter($reflectionParameter);
                    $parameterProcessed = true;
                } elseif ($attribute->getName() === Parameter::class) {
                    if ($parameterProcessed) {
                        throw new \DomainException(
                            'Method parameter ' . $reflectionMethod->getDeclaringClass()->name
                            . '::' . $reflectionMethod->getName() . '::' . $reflectionParameter->name
                            . ' must be attributed as either OpenAPI Parameter or RequestBody and was already attributed'
                        );
                    }
                    $parameters[] = OpenApiParameter::fromReflectionParameter($reflectionParameter);
                    $parameterProcessed = true;
                }
            }
            if (!$parameterProcessed) {
                throw new \DomainException(
                    'Method parameter ' . $reflectionMethod->getDeclaringClass()->name
                    . '::' . $reflectionMethod->getName() . '::' . $reflectionParameter->name
                    . ' must be attributed as either OpenAPI Parameter or RequestBody but was not attributed at all'
                );
            }
        }

        return new self(
            $pathMetadata->pathDefinition,
            $pathMetadata->httpMethod,
            new OpenApiParameterCollection(...$parameters),
            $requestBody,
            OpenApiResponses::fromReflectionMethod($reflectionMethod),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter([
            'parameters' => $this->parameters->isEmpty() ? null : $this->parameters,
            'requestBody' => $this->requestBody,
            'responses' => $this->responses,
        ]);
    }
}
