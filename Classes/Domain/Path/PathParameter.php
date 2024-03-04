<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class PathParameter implements \JsonSerializable
{
    /**
     * @param array<string, mixed> $schema
     */
    public function __construct(
        public string $name,
        public bool $required,
        public array $schema,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
