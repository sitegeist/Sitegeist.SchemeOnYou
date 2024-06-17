<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\InvalidObjects;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class ObjectThatHasNotSupportedProperties
{
    public function __construct(
        public \Psr\Http\Message\RequestInterface $text
    ) {
    }
}
