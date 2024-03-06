<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema(description: 'see https://schema.org/PostalAddress')]
#[Flow\Proxy(false)]
final readonly class PostalAddress implements \JsonSerializable
{
    public function __construct(
        public string $streetAddress,
        public ?string $addressRegion,
        public ?string $addressCountry = 'DE',
        public ?string $postOfficeBoxNumber = null
    ) {
    }

    /**
     * @param array<string,mixed> $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            $values['streetAddress'],
            $values['addressRegion'],
            $values['addressCountry'] ?? 'DE',
            $values['postOfficeBoxNumber'] ?? null,
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
