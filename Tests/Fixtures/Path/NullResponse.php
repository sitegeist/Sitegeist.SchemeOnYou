<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema('the null response')]
#[OpenApi\PathResponse(statusCode: 200, description: 'the query resulted in null')]
#[Flow\Proxy(false)]
final readonly class NullResponse implements \JsonSerializable
{
    public function __construct(
        public null $value = null,
    ) {
    }

    /**
     * @param array<null> $values
     */
    public static function fromArray(array $values): self
    {
        return new self($values['value']);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
