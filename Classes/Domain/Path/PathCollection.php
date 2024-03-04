<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

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
     * @param array<class-string,array<string>> $methodNames indexed by class name
     */
    public static function fromMethodNames(array $methodNames): self
    {
        $paths = [];
        foreach ($methodNames as $className => $methodNamesForClass) {
            foreach ($methodNamesForClass as $methodName) {
                $paths[] = Path::fromMethodName($className, $methodName);
            }
        }
        return new self(...$paths);
    }

    /**
     * @return array<string,array<string,Path>>
     */
    public function jsonSerialize(): array
    {
        $result = [];
        foreach ($this->items as $item) {
            $result[$item->uriPath][$item->httpMethod->value] = $item;
        }

        return $result;
    }
}
