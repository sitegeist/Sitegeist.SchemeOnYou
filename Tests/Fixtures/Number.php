<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema('see https://schema.org/Number')]
#[Flow\Proxy(false)]
final readonly class Number
{
    public function __construct(
        public float $value,
    ) {
    }
}
