<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Stuff;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
readonly class ClassWithStuffViaOptionalUnion
{
    public function __construct(
        public BoringStuff|InterestingStuff|null $stuff = null
    ) {
    }
}
