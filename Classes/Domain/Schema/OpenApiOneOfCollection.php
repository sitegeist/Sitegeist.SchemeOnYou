<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class OpenApiOneOfCollection implements \JsonSerializable
{
    /** @var array<OpenApiReference> */
    private array $items;

    public function __construct(
        OpenApiReference ...$items
    ) {
        $this->items = $items;
    }

    /**
     * @param array<class-string> $classNames
     */
    public static function fromClassNames(array $classNames): self
    {
        return new self(...array_map(
            fn (string $className): OpenApiReference => OpenApiReference::fromClassName($className),
            $classNames
        ));
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_map(
            fn(OpenApiReference $item) => [
                'allOf' => [
                    [
                        'type' => 'object',
                        'properties' => [
                            OpenApiSchemaDiscriminator::DISCRIMINATOR_NAME => [
                                'type' => 'string',
                                'enum' => [$item->getName()],
                                'required' => true
                            ]
                        ]
                    ],
                    $item
                ]
            ],
            $this->items
        );
    }
}
