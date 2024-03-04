<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\Definition;

#[Definition(description: 'a collection of postal addresses, see https://schema.org/PostalAddress')]
#[Flow\Proxy(false)]
final readonly class PostalAddressCollection implements \JsonSerializable
{
    /**
     * @var array<PostalAddress>
     */
    private array $items;

    public function __construct(
        PostalAddress... $items
    ) {
        $this->items = $items;
    }

    /**
     * @return array<PostalAddress>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
