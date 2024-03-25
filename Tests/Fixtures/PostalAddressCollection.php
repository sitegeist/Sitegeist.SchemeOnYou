<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema(description: 'a collection of postal addresses, see https://schema.org/PostalAddress')]
#[Flow\Proxy(false)]
final readonly class PostalAddressCollection
{
    /**
     * @var array<PostalAddress>
     */
    public array $items;

    public function __construct(
        PostalAddress ...$items
    ) {
        $this->items = $items;
    }
}
