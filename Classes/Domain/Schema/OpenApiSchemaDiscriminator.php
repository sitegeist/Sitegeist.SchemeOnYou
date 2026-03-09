<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;

/**
 * @see https://swagger.io/specification/#reference-object
 */
#[Flow\Proxy(false)]
final readonly class OpenApiSchemaDiscriminator implements \JsonSerializable
{
    public const DISCRIMINATOR_NAME = '__type__';

    public function __construct(
        public string $propertyName = self::DISCRIMINATOR_NAME,
    ) {
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'propertyName' => $this->propertyName
        ];
    }
}
