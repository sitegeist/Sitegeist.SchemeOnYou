<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema(description: 'a thing composed of all primitive types')]
#[Flow\Proxy(false)]
final readonly class WeirdThing
{
    public function __construct(
        public bool $if,
        public string $what,
        public int $howMuch,
        public float $howMuchPrecisely,
        public \DateTimeImmutable $when,
        public \DateInterval $howLong,
        public UriInterface $where,
    ) {
    }
}
