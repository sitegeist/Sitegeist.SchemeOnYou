<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema(description: 'see https://schema.org/PostalAddress')]
#[Flow\Proxy(false)]
final readonly class PostalAddress
{
    public function __construct(
        public string $streetAddress,
        public ?string $addressRegion,
        public ?string $addressCountry = 'DE',
        public ?string $postOfficeBoxNumber = null
    ) {
    }
}
