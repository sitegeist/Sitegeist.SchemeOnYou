<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class OpenApiPathCollection implements \JsonSerializable
{
    /** @var array<OpenApiPathItem> */
    private array $items;

    public function __construct(
        OpenApiPathItem ...$items
    ) {
        $this->items = $items;
    }

    /**
     * @param array<class-string,array<string>> $methodNames indexed by class name
     */
    public static function fromMethodNames(array $methodNames): self
    {
        $paths = [];
        foreach ($methodNames as $className => $methodNamesForClass) {
            foreach ($methodNamesForClass as $methodName) {
                $paths[] = OpenApiPathItem::fromMethodName($className, $methodName);
            }
        }
        return new self(...$paths);
    }

    public function merge(OpenApiPathCollection $other): OpenApiPathCollection
    {
        return new OpenApiPathCollection(...$this->items, ...$other->items);
    }

    /**
     * @return array<string,array<string,OpenApiPathItem>>
     */
    public function jsonSerialize(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            $result[$item->pathDefinition->value][$item->httpMethod->value] = $item;
        }

        return $result;
    }
}
