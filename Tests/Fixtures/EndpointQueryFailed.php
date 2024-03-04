<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as Scheme;

#[Scheme\Definition('the endpoint query failure response')]
#[Scheme\PathResponse(statusCode: 400, description: 'the query failed')]
#[Flow\Proxy(false)]
final readonly class EndpointQueryFailed implements \JsonSerializable
{
    public function __construct(
        public string $reason,
    ) {
    }

    /**
     * @param array<string> $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values['reason']);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
