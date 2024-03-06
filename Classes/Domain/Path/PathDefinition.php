<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;

/**
 * @see https://swagger.io/specification/#paths-object
 */
#[Flow\Proxy(false)]
final readonly class PathDefinition
{
    public function __construct(
        public string $value,
    ) {
        if (!str_starts_with($value, '/')) {
            throw new \DomainException('Path definitions must start with /', 1709713603);
        }
    }
}
