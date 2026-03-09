<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Stuff;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
readonly class BoringStuff implements StuffInterface
{
    public function __construct(
        public int $number
    ) {
    }
}
