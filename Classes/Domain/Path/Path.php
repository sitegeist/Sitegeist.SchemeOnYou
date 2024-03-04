<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\HttpMethod;
use Sitegeist\SchemeOnYou\Domain\Metadata\Path as PathMetadata;

#[Flow\Proxy(false)]
final readonly class Path implements \JsonSerializable
{
    public function __construct(
        public string $uriPath,
        public HttpMethod $httpMethod,
        public PathParameterCollection $parameters,
        public PathResponseCollection $responses,
    ) {
    }

    /**
     * @param class-string $className
     */
    public static function fromMethodName(string $className, string $methodName): self
    {
        $reflectionClass = new \ReflectionClass($className);
        $reflectionMethod = $reflectionClass->getMethod($methodName);

        $pathMetadata = PathMetadata::fromReflection($reflectionMethod);

        return new self(
            $pathMetadata->uriPath,
            $pathMetadata->httpMethod,
            PathParameterCollection::fromMethodArguments($reflectionMethod),
            PathResponseCollection::fromReflectionMethod($reflectionMethod),
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
