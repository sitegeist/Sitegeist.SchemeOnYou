<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

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

    public static function fromClassName(string $className): self
    {
        return new self('#/components/schemas/' . str_replace('\\', '_', $className));
    }

    public function getName(): string
    {
        return str_replace('#/components/schemas/', '', $this->ref);
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
