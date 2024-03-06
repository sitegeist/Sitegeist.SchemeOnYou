<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;
use Sitegeist\SchemeOnYou\Domain\Path\RequestParameterContract;

#[OpenApi\Schema('the endpoint query')]
#[Flow\Proxy(false)]
final readonly class EndpointQuery implements RequestParameterContract
{
    public function __construct(
        public string $language,
    ) {
    }

    /**
     * @param string $parameter
     */
    public static function fromRequestParameter(mixed $parameter): static
    {
        return new self($parameter);
    }

    public function jsonSerialize(): string
    {
        return $this->language;
    }
}
