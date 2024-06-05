<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class OpenApiQueryPart
{
    private function __construct(
        public readonly string $name,
        public readonly ?string $value
    ) {
    }

    public static function fromQueryStringPart(string $queryStringPart): self
    {
        $parts = explode('=', $queryStringPart, 2);
        return new self($parts[0], $parts[1] ?? null);
    }
}
