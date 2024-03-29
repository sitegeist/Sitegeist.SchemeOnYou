<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema('see https://schema.org/identifier')]
#[Flow\Proxy(false)]
final readonly class Identifier
{
    public function __construct(
        public string $value,
    ) {
    }
}
