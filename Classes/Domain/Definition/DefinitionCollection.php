<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Definition;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Definition\Definition;

#[Flow\Proxy(false)]
final readonly class DefinitionCollection implements \JsonSerializable
{
    /** @var array<Definition> */
    private array $items;

    public function __construct(
        Definition ...$items
    ) {
        $this->items = $items;
    }

    /**
     * @param array<class-string> $classNames
     */
    public static function fromClassNames(array $classNames): self
    {
        return new self(...array_map(
            fn (string $className): Definition => Definition::fromClassName($className),
            $classNames
        ));
    }

    /**
     * @return array<Definition>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
