<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;
use Traversable;

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

    public static function fromMethodArguments(\ReflectionMethod $reflectionMethod): self
    {
        return new self(...array_map(
            fn (\ReflectionParameter $reflectionParameter): OpenApiParameter
                => OpenApiParameter::fromReflectionParameter($reflectionParameter),
            $reflectionMethod->getParameters()
        ));
    }

    /**
     * @return array<OpenApiParameter>
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
