<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Path\PathDefinition;

#[Flow\Proxy(false)]
#[\Attribute(\Attribute::TARGET_METHOD)]
final readonly class Path
{
    public PathDefinition $pathDefinition;
    public HttpMethod $httpMethod;

    public function __construct(
        string|PathDefinition $pathDefinition,
        string|HttpMethod $httpMethod,
    ) {
        $this->pathDefinition = is_string($pathDefinition) ? new PathDefinition($pathDefinition) : $pathDefinition;
        $this->httpMethod = is_string($httpMethod) ? HttpMethod::from($httpMethod) : $httpMethod;
    }

    /**
     * @param \ReflectionMethod $reflectionMethod
     */
    public static function fromReflectionMethod(\ReflectionMethod $reflectionMethod): self
    {
        $pathReflections = $reflectionMethod->getAttributes(Path::class);
        if (count($pathReflections) !== 1) {
            throw new \DomainException(
                'There must be exactly one path attribute declared in method '
                . $reflectionMethod->class . '::' . $reflectionMethod->name . ', ' . count($pathReflections) . ' given',
                1709584594
            );
        }
        $arguments = $pathReflections[0]->getArguments();

        return new self(
            $arguments['pathDefinition'] ?? $arguments[0],
            $arguments['httpMethod'] ?? $arguments[1],
        );
    }
}
