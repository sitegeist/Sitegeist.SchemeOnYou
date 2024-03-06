<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\HttpMethod;
use Sitegeist\SchemeOnYou\Domain\Metadata\Path as PathMetadata;

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
        public OpenApiResponses $responses,
    ) {
    }

    /**
     * @param class-string $className
     */
    public static function fromMethodName(string $className, string $methodName): self
    {
        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethod = $reflectionClass->getMethod($methodName);

        return self::fromReflectionMethod($reflectionMethod);
    }

    public static function fromReflectionMethod(\ReflectionMethod $reflectionMethod): self
    {
        $pathMetadata = PathMetadata::fromReflection($reflectionMethod);

        return new self(
            $pathMetadata->pathDefinition,
            $pathMetadata->httpMethod,
            OpenApiParameterCollection::fromMethodArguments($reflectionMethod),
            OpenApiResponses::fromReflectionMethod($reflectionMethod),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'parameters' => $this->parameters,
            'responses' => $this->responses,
        ];
    }
}
