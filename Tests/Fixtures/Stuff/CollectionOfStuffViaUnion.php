<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Stuff;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
readonly class CollectionOfStuffViaUnion
{
    /**
     * @var array<int|string, BoringStuff|InterestingStuff>
     */
    public array $items;

    public function __construct(BoringStuff|InterestingStuff ...$items)
    {
        $this->items = $items;
    }
}
