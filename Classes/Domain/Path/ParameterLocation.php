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
     * @return NoSuchParameter|array<mixed>|int|bool|string|float|null
     */
    public function resolveParameterFromRequest(ActionRequest $request, string $parameterName): NoSuchParameter|array|int|bool|string|float|null
    {
        return match ($this) {
            ParameterLocation::LOCATION_PATH => $request->hasArgument($parameterName) ? $request->getArgument($parameterName) : new NoSuchParameter(),
            ParameterLocation::LOCATION_QUERY => array_key_exists($parameterName, $request->getHttpRequest()->getQueryParams()) ? $request->getHttpRequest()->getQueryParams()[$parameterName] : new NoSuchParameter(),
            ParameterLocation::LOCATION_HEADER => $request->getHttpRequest()->hasHeader($parameterName) ? $request->getHttpRequest()->getHeader($parameterName) : new NoSuchParameter(),
            ParameterLocation::LOCATION_COOKIE => $request->getHttpRequest()->getCookieParams()[$parameterName] ?? new NoSuchParameter(),
        };
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
