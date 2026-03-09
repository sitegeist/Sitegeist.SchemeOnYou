<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Stuff;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
readonly class CollectionOfStuffViaInterface
{
    /**
     * @var StuffInterface[]
     */
    public array $items;

    public function __construct(StuffInterface ...$items)
    {
        $this->items = $items;
    }
}
