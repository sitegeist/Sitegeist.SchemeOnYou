<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema('see https://schema.org/Number')]
#[Flow\Proxy(false)]
final readonly class Number implements \JsonSerializable
{
    public function __construct(
        public float $value,
    ) {
    }

    public static function fromFloat(float $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): float
    {
        return $this->value;
    }
}
