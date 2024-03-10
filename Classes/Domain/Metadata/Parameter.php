<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterLocation;

/**
 * @see https://swagger.io/specification/#parameter-object
 */
#[Flow\Proxy(false)]
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final readonly class Parameter
{
    public function __construct(
        public ParameterLocation $in,
        public ?string $description = null,
    ) {
    }

    public static function tryFromReflectionParameter(\ReflectionParameter $reflectionParameter): ?self
    {
        $parameterAttributes = $reflectionParameter->getAttributes(self::class);
        if (count($parameterAttributes) === 1) {
            $arguments = $parameterAttributes[0]->getArguments();

            return new self(
                $arguments['in'] ?? $arguments[0],
                $arguments['description'] ?? $arguments[1] ?? null,
            );
        }

        return null;
    }

    public static function fromReflectionParameter(\ReflectionParameter $reflectionParameter): self
    {
        $parameter = self::tryFromReflectionParameter($reflectionParameter);
        if (!$parameter) {
            $parameterAttributes = $reflectionParameter->getAttributes(self::class);
            throw new \DomainException(
                'There must be exactly one parameter attribute declared in parameter '
                . $reflectionParameter->getDeclaringClass()?->name
                . '::' . $reflectionParameter->getDeclaringFunction()->name
                . '::' . $reflectionParameter->name
                . ', ' . count($parameterAttributes) . ' given',
                1709656717
            );
        }

        return $parameter;
    }
}
