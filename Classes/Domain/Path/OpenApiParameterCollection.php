<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;

/**
 * @implements \IteratorAggregate<OpenApiParameter>
 */
#[Flow\Proxy(false)]
final readonly class OpenApiParameterCollection implements \JsonSerializable, \IteratorAggregate
{
    /** @var array<OpenApiParameter> */
    private array $items;

    public function __construct(OpenApiParameter ...$items)
    {
        $this->items = $items;
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }

    /**
     * @return array<OpenApiParameter>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }

    /**
     * @return \Traversable<OpenApiParameter>
     */
    public function getIterator(): \Traversable
    {
        yield from $this->items;
    }
}
