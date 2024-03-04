<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Sitegeist\SchemeOnYou\Domain\Metadata as Scheme;

#[Scheme\Definition('important numbers only')]
enum ImportantNumber: int
{
    case NUMBER_23 = 23;
    case NUMBER_42 = 42;
}
