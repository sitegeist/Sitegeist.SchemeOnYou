<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\InvalidObjects;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final class ObjectThatIsNotReadonly
{
    public function __construct(
        public string $text,
        public int $num,
        public bool $switch
    ) {
    }
}
