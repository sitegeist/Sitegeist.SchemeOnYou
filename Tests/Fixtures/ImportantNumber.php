<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema('important numbers only')]
enum ImportantNumber: int implements \JsonSerializable
{
    case NUMBER_23 = 23;
    case NUMBER_42 = 42;

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
