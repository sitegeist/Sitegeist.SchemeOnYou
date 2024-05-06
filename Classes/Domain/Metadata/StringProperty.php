<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Annotations as Flow;

/**
 * @see https://swagger.io/docs/specification/data-models/data-types/#string
 */
#[Flow\Proxy(false)]
#[\Attribute(\Attribute::TARGET_PROPERTY)]
final readonly class StringProperty
{
    public const FORMAT_DATE = 'date';
    public const FORMAT_DATE_TIME = 'date-time';
    public const FORMAT_PASSWORD = 'password';
    public const FORMAT_BYTE = 'byte';
    public const FORMAT_BINARY = 'binary';

    public function __construct(
        public ?string $format = null,
        public ?string $description = null,
    ) {
    }

    public static function tryFromReflectionParameter(\ReflectionParameter $reflectionParameter): ?self
    {
        $parameterAttributes = $reflectionParameter->getAttributes(self::class);
        switch (count($parameterAttributes)) {
            case 0:
                return null;
            case 1:
                $arguments = $parameterAttributes[0]->getArguments();

                return new self(
                    $arguments['format'] ?? $arguments[0] ?? null,
                    $arguments['description'] ?? $arguments[1] ?? null,
                );
            default:
                throw new \DomainException(
                    'There must be no or exactly one string property attribute declared in parameter '
                    . $reflectionParameter->getDeclaringClass()?->name
                    . '::' . $reflectionParameter->getDeclaringFunction()->name
                    . '::' . $reflectionParameter->name
                    . ', ' . count($parameterAttributes) . ' given',
                    1715000621
                );
        }
    }
}
