<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class Schema
{
    /**
     * @phpstan-param class-string $className
     */
    public static function fromClassName(string $className): self
    {
        return new self();
    }
}
