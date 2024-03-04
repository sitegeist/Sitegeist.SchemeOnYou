<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as Scheme;

#[Scheme\Definition('see https://schema.org/QuantitativeValue')]
#[Flow\Proxy(false)]
final readonly class QuantitativeValue implements \JsonSerializable
{
    public function __construct(
        public int $value,
    ) {
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function jsonSerialize(): int
    {
        return $this->value;
    }
}
