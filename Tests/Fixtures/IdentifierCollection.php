<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[Flow\Proxy(false)]
final readonly class IdentifierCollection
{
    /**
     * @var Identifier[]
     */
    public array $items;
    public function __construct(
        Identifier ...$items,
    ) {
        $this->items = $items;
    }
}
