<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;

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
