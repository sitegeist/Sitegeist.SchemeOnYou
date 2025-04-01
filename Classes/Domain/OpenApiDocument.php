<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiPathCollection;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchemaCollection;

/**
 * @see https://swagger.io/specification/#openapi-object
 */
#[Flow\Proxy(false)]
final readonly class OpenApiDocument implements \JsonSerializable
{
    /**
     * @param array<string,mixed> $info @todo replace with object
     * @param array<int,mixed> $servers @todo replace with object
     * @param array<string,mixed> $webhooks @todo replace with object
     * @param array<int,mixed> $security @todo replace with object
     * @param array<int,mixed> $tags @todo replace with tag set
     * @param array<string,mixed> $externalDocs @todo replace with object
     */
    public function __construct(
        public string $openapi,
        public array $info,
        public array $servers,
        public OpenApiPathCollection $paths,
        public array $webhooks,
        private OpenApiComponents $components,
        public array $security,
        public array $tags,
        public array $externalDocs,
    ) {
    }

    /**
     * @param array<string,mixed> $configuration
     */
    public static function createFromConfiguration(
        array $configuration,
        ?OpenApiPathCollection $paths = null,
        ?OpenApiComponents $components = null,
    ): self {
        return new self(
            $configuration['openapi'],
            $configuration['info'],
            $configuration['servers'],
            $paths ?: new OpenApiPathCollection(),
            $configuration['webhooks'],
            $components ?: new OpenApiComponents(
                new OpenApiSchemaCollection()
            ),
            $configuration['security'],
            $configuration['tags'],
            $configuration['externalDocs'],
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
