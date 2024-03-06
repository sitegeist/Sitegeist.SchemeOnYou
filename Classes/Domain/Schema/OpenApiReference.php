<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\Schema;

/**
 * @see https://swagger.io/specification/#reference-object
 */
#[Flow\Proxy(false)]
final readonly class OpenApiReference implements \JsonSerializable
{
    public function __construct(
        public string $ref,
    ) {
    }

    public static function fromSchemaName(string $schemaName): self
    {
        return new self(
            '#/components/schemas/' . $schemaName
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            '$ref' => $this->ref,
        ];
    }
}
