<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiReference;
use Traversable;

/**
 * @implements \IteratorAggregate<OpenApiParameter|OpenApiReference>
 */
#[Flow\Proxy(false)]
final readonly class OpenApiParameterCollection implements \JsonSerializable, \IteratorAggregate
{
    /** @var array<OpenApiParameter|OpenApiReference> */
    private array $items;

    public function __construct(
        OpenApiParameter|OpenApiReference ...$items
    ) {
        $this->items = $items;
    }

    public static function fromMethodArguments(\ReflectionMethod $reflectionMethod): self
    {
        return new self(...array_map(
            fn (\ReflectionParameter $reflectionParameter): OpenApiParameter
                => OpenApiParameter::fromReflectionParameter($reflectionParameter),
            $reflectionMethod->getParameters()
        ));
    }

    /**
     * @return array<OpenApiParameter|OpenApiReference>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }

    public function getIterator(): Traversable
    {
        yield from $this->items;
    }
}
