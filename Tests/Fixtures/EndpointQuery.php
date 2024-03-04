<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as Scheme;

#[Scheme\Definition('the endpoint query')]
#[Flow\Proxy(false)]
final readonly class EndpointQuery implements \JsonSerializable
{
    public function __construct(
        public string $language,
    ) {
    }

    /**
     * @param array<string> $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values['language']);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
