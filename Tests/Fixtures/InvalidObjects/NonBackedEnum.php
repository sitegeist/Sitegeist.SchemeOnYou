<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\InvalidObjects;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
enum NonBackedEnum
{
    case Foo;
    case Bar;
}
