<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class OpenApiSchemaOrReferenceCollection implements \JsonSerializable
{
    /** @var array<OpenApiSchema|OpenApiReference> */
    private array $items;

    public function __construct(
        OpenApiSchema|OpenApiReference ...$items
    ) {
        $this->items = $items;
    }

    /**
     * @return array<string, OpenApiSchema|OpenApiReference>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
