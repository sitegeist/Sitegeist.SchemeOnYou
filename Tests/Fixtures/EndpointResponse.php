<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as Schema;

#[Schema\Definition('the endpoint response')]
#[Schema\PathResponse(statusCode: 200, description: 'the query was successful')]
#[Flow\Proxy(false)]
final readonly class EndpointResponse
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
