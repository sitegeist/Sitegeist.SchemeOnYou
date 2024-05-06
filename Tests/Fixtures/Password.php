<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema(description: 'a password')]
#[Flow\Proxy(false)]
final readonly class Password
{
    public function __construct(
        #[OpenApi\StringProperty(format: OpenApi\StringProperty::FORMAT_PASSWORD)]
        public string $value,
    ) {
    }
}
