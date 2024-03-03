<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
#[\Attribute]
final readonly class Description
{
    public function __construct(
        public string $value,
    ) {
    }
}
