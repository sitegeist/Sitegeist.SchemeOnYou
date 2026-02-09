<?php
declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Stuff;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
readonly class WeirdStuff  implements StuffInterface
{
    public function __construct(
        public \DateTimeImmutable $date,
        public bool $whatever
    ) {
    }
}
