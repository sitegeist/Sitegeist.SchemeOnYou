<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema('the endpoint response')]
#[OpenApi\Response(statusCode: 200, description: 'the query was successful')]
#[Flow\Proxy(false)]
final readonly class EndpointResponse implements \JsonSerializable
{
    public function __construct(
        public string $thing,
    ) {
    }

    /**
     * @param array<string> $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values['thing']);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
