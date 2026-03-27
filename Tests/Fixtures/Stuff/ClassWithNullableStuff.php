<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Stuff;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
readonly class ClassWithNullableStuff
{
    public function __construct(
        public ?string $name,
        public ?BoringStuff $stuff
    ) {
    }
}
