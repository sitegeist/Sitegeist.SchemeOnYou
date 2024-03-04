<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class Path
{
    public static function fromClassName(string $className): self
    {
        throw new \DomainException('Cannot create path from incomprehensible type ' . $className, 1709539343);
    }
}
