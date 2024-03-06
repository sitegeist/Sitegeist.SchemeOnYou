<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;
use Sitegeist\SchemeOnYou\Domain\Path\RequestParameterContract;

#[OpenApi\Schema('another endpoint query')]
#[Flow\Proxy(false)]
final readonly class AnotherEndpointQuery implements RequestParameterContract
{
    public function __construct(
        public bool $pleaseFail,
    ) {
    }

    /**
     * @param string $parameter
     */
    public static function fromRequestParameter($parameter): static
    {
        return new self(\json_decode($parameter, true, 512, JSON_THROW_ON_ERROR)['pleaseFail']);
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
