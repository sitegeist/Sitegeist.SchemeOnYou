<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class ReflectionDescriptionCollection
{
    /**
     * @var array<\ReflectionAttribute<Description>>
     */
    private array $items;

    /**
     * @param \ReflectionAttribute<Description> ...$items
     */
    public function __construct(
        \ReflectionAttribute ...$items
    ) {
        $this->items = $items;
    }

    public static function fromReflection(\ReflectionClass|\ReflectionEnum $reflection): self
    {
        return new self(...$reflection->getAttributes(Description::class));
    }

    public function render(): string
    {
        return implode("\n", array_filter(array_map(
            fn (\ReflectionAttribute $description): ?string => $description->getArguments()[0] ?? null,
            $this->items
        )));
    }
}
