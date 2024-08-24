<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\InvalidObjects;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Tests\Fixtures;

#[Flow\Proxy(false)]
final readonly class CollectionWithTooManyConstructorArguments
{
    /**
     * @var Fixtures\Identifier[]
     */
    public array $items;
    public function __construct(
        string $other,
        Fixtures\Identifier ...$items
    ) {
        if ($other === 'whatever') {
            $this->items = [...$items];
        } else {
            $this->items = $items;
        }
    }
}
