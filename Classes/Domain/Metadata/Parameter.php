<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterLocation;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterStyle;

/**
 * @see https://swagger.io/specification/#parameter-object
 */
#[Flow\Proxy(false)]
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final readonly class Parameter
{
    public ParameterStyle $style;

    public function __construct(
        public ParameterLocation $in,
        ?ParameterStyle $style = null,
        public ?string $description = null,
    ) {
        $this->style = $style ?: ParameterStyle::createDefaultForParameterLocation($this->in);
    }

    public static function fromReflectionParameter(\ReflectionParameter $reflectionParameter): self
    {
        $parameterAttributes = $reflectionParameter->getAttributes(self::class);
        switch (count($parameterAttributes)) {
            case 0:
                return new self(
                    in: ParameterLocation::LOCATION_QUERY,
                    style: ParameterStyle::createDefaultForParameterLocationAndReflection(ParameterLocation::LOCATION_QUERY, $reflectionParameter)
                );
            case 1:
                $arguments = $parameterAttributes[0]->getArguments();

                return new self(
                    $arguments['in'] ?? $arguments[0],
                    $arguments['style'] ?? $arguments[1] ?? ParameterStyle::createDefaultForParameterLocationAndReflection($arguments['in'] ?? $arguments[0], $reflectionParameter),
                    $arguments['description'] ?? $arguments[2] ?? null,
                );
            default:
                throw new \DomainException(
                    'There must be no or exactly one parameter attribute declared in parameter '
                    . $reflectionParameter->getDeclaringClass()?->name
                    . '::' . $reflectionParameter->getDeclaringFunction()->name
                    . '::' . $reflectionParameter->name
                    . ', ' . count($parameterAttributes) . ' given',
                    1709656717
                );
        }
    }
}
