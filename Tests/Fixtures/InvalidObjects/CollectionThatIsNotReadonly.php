<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\InvalidObjects;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Tests\Fixtures;

#[Flow\Proxy(false)]
final class CollectionThatIsNotReadonly
{
    /**
     * @var Fixtures\Identifier[]
     */
    public array $items;
    public function __construct(
        Fixtures\Identifier ...$items
    ) {
        $this->items = $items;
    }
}
