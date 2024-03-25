<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema('the endpoint query')]
#[Flow\Proxy(false)]
final readonly class EndpointQuery
{
    public function __construct(
        public string $language,
    ) {
    }
}
