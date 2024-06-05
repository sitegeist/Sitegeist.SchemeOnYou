<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Mvc\ActionRequest;

/**
 * @see https://swagger.io/specification/#parameter-locations
 */
enum ParameterLocation: string implements \JsonSerializable
{
    case LOCATION_PATH = 'path';
    case LOCATION_QUERY = 'query';
    case LOCATION_HEADER = 'header';
    case LOCATION_COOKIE = 'cookie';

    /**
     * @todo really?
     * @return array<mixed>|int|bool|string|float|null
     */
    public function resolveParameterFromRequest(ActionRequest $request, string $parameterName): array|int|bool|string|float|null
    {
        return match ($this) {
            ParameterLocation::LOCATION_PATH => $request->hasArgument($parameterName) ? $request->getArgument($parameterName) : null,
            ParameterLocation::LOCATION_QUERY => $this->resolveQueryParameterFromRequest($request, $parameterName),
            ParameterLocation::LOCATION_HEADER => $request->getHttpRequest()->hasHeader($parameterName) ? $request->getHttpRequest()->getHeader($parameterName) : null,
            ParameterLocation::LOCATION_COOKIE => $request->getHttpRequest()->getCookieParams()[$parameterName] ?? null,
        };
    }

    /**
     * @return null|true|string|string[]
     */
    public function resolveQueryParameterFromRequest(ActionRequest $request, string $parameterName): null|true|string|array
    {
        $query = OpenApiQuery::fromQueryString($request->getHttpRequest()->getUri()->getQuery());
        return $query->findParameterValue($parameterName, ParameterStyle::STYLE_FORM, true);
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
