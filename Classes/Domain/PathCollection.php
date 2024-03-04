<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class PathCollection implements \JsonSerializable
{
    /** @var array<Path> */
    private array $items;

    public function __construct(
        Path ...$items
    ) {
        $this->items = $items;
    }

    /**
     * @param array<class-string> $classNames
     */
    public static function fromClassNames(array $classNames): self
    {
        return new self(...array_map(
            fn (string $className): Path => Path::fromClassName($className),
            $classNames
        ));
    }

    /**
     * @return array<Path>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
