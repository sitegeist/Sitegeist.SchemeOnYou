<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
class OpenApiQuery
{
    /**
     * @var OpenApiQueryPart[]
     */
    private array $queryParts;

    private function __construct(OpenApiQueryPart ...$queryParts)
    {
        $this->queryParts = $queryParts;
    }

    public static function fromQueryString(string $queryString): self
    {
        $parts = explode('&', rawurldecode($queryString));
        return new self(...array_map(
            fn(string $part) => OpenApiQueryPart::fromQueryStringPart($part),
            $parts
        ));
    }

    /**
     * @param string $name
     * @param ParameterStyle $style
     * @param bool $explode
     * @return null|true|string|string[]
     */
    public function findParameterValue(
        string $name,
        ParameterStyle $style = ParameterStyle::STYLE_FORM,
        bool $explode = false
    ): null|true|string|array {
        return match ($style) {
            ParameterStyle::STYLE_FORM => $this->findFormParameterValue($name, $explode),
            ParameterStyle::STYLE_DEEP_OBJECT => $this->findDeepObjectParameterValue($name, $explode),
            ParameterStyle::STYLE_PIPE_DELIMITED => $this->findCharacterSeperatedParameterValue($name, $explode, '|'),
            ParameterStyle::STYLE_SPACE_DELIMITED => $this->findCharacterSeperatedParameterValue($name, $explode, ' '),
            default => throw new \Exception('Unexpected parameter style in a query')
        };
    }

    /**
     * @return null|true|string|string[]
     */
    private function findFormParameterValue(string $name, bool $explode): null|true|string|array
    {
        $parts = $this->findQueryPartsByName($name);
        if (count($parts) == 0) {
            return null;
        }
        if ($explode === false) {
            $part = array_pop($parts);
            if ($part->value === null) {
                return true;
            } else {
                $values = explode(',', $part->value);
                return count($values) === 1 ? $values[0] : $values;
            }
        } else {
            $values = array_filter(
                array_values(
                    array_map(
                        fn(OpenApiQueryPart $part) => $part->value,
                        $parts
                    )
                )
            );

            return match (count($values)) {
                0 => null,
                1 => $values[0],
                default => $values
            };
        }
    }

    /**
     * @return null|true|string|string[]
     */
    private function findDeepObjectParameterValue(string $name, bool $explode): null|true|string|array
    {
        throw new \Exception('not implemented yet');
    }

    /**
     * @return null|true|string|string[]
     */
    private function findCharacterSeperatedParameterValue(string $name, bool $explode, string $separator): null|true|string|array
    {
        throw new \Exception('not implemented yet');
    }

    /**
     * @param string $name
     * @return OpenApiQueryPart[]
     */
    private function findQueryPartsByName(string $name): array
    {
        return array_filter(
            $this->queryParts,
            fn(OpenApiQueryPart $part) => $part->name === $name
        );
    }
}
