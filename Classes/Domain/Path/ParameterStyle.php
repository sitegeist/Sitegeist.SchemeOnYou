<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

/**
 * @see https://swagger.io/specification/#style-values
 */
enum ParameterStyle: string implements \JsonSerializable
{
    case STYLE_MATRIX = 'matrix';
    case STYLE_LABEL = 'label';
    case STYLE_FORM = 'form';
    case STYLE_SIMPLE = 'simple';
    case STYLE_SPACE_DELIMITED = 'spaceDelimited';
    case STYLE_PIPE_DELIMITED = 'pipeDelimited';
    case STYLE_DEEP_OBJECT = 'deepObject';

    /**
     * @see https://swagger.io/specification/#fixed-fields-10
     */
    public static function createDefaultForParameterLocation(ParameterLocation $location): self
    {
        return match ($location) {
            ParameterLocation::LOCATION_QUERY => ParameterStyle::STYLE_FORM,
            ParameterLocation::LOCATION_PATH => ParameterStyle::STYLE_SIMPLE,
            ParameterLocation::LOCATION_HEADER => ParameterStyle::STYLE_SIMPLE,
            ParameterLocation::LOCATION_COOKIE => ParameterStyle::STYLE_FORM
        };
    }

    /**
     * @todo really?
     * @param array<mixed>|int|bool|string|float|null $parameterValue
     * @return array<mixed>|int|bool|string|float|null
     */
    public function decodeParameterValue(array|int|bool|string|float|null $parameterValue): array|int|bool|string|float|null
    {
        return match ($this) {
            self::STYLE_DEEP_OBJECT => match (true) {
                $parameterValue === null => $parameterValue,
                is_string($parameterValue) => \json_decode($parameterValue, true, 512, JSON_THROW_ON_ERROR),
                default => throw new \DomainException('Parameters with deepObject style must be sent as JSON or null')
            },
            default => $parameterValue,
        };
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
