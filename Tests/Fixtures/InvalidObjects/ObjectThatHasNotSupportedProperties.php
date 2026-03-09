<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\InvalidObjects;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;

#[Flow\Proxy(false)]
final readonly class ObjectThatHasNotSupportedProperties
{
    public function __construct(
        public ActionRequest $text
    ) {
    }
}
