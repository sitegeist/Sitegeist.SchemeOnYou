<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchemaCollection;

/**
 * @see https://swagger.io/specification/#components-object
 */
#[Flow\Proxy(false)]
final readonly class OpenApiComponents implements \JsonSerializable
{
    /**
     * @param array<string,mixed> $responses @todo replace with object
     * @param array<string,mixed> $parameters @todo replace with object
     * @param array<string,mixed> $examples @todo replace with object
     * @param array<string,mixed> $requestBodies @todo replace with object
     * @param array<string,mixed> $headers @todo replace with object
     * @param array<string,mixed> $securitySchemes @todo replace with object
     * @param array<string,mixed> $links @todo replace with object
     * @param array<string,mixed> $callbacks @todo replace with object
     * @param array<string,mixed> $pathItems @todo replace with object
     */
    public function __construct(
        public OpenApiSchemaCollection $schemas,
        public array $responses = [],
        public array $parameters = [],
        public array $examples = [],
        public array $requestBodies = [],
        public array $headers = [],
        public array $securitySchemes = [],
        public array $links = [],
        public array $callbacks = [],
        public array $pathItems = [],
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
