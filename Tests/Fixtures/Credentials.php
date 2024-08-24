<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema(description: 'credentials')]
#[Flow\Proxy(false)]
final readonly class Credentials
{
    public function __construct(
        #[OpenApi\StringProperty(description: 'a username')]
        public string $username,
        public Password $password,
        #[OpenApi\StringProperty(format: OpenApi\StringProperty::FORMAT_DATE)]
        public \DateTimeImmutable $expirationDate,
    ) {
    }
}
