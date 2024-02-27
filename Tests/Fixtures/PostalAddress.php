<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;

/**
 * @see https://schema.org/PostalAddress
 */
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
