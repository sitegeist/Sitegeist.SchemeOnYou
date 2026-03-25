<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Stuff;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[Flow\Proxy(false)]
#[OpenApi\Schema('', name: 'Interesting Stuff')]
readonly class InterestingStuff implements StuffInterface
{
    public function __construct(
        public string $gossip
    ) {
    }
}
