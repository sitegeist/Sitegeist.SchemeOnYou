<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Mvc\ActionRequest;

enum ParameterLocation: string implements \JsonSerializable
{
    case LOCATION_PATH = 'path';
    case LOCATION_QUERY = 'query';
    case LOCATION_HEADER = 'header';
    case LOCATION_COOKIE = 'cookie';

    /**
     * @todo really?
     * @return object|array<mixed>|int|bool|string|float|null
     */
    public function resolveParameterFromRequest(ActionRequest $request, string $parameterName): object|array|int|bool|string|float|null
    {
        return match ($this) {
            ParameterLocation::LOCATION_PATH => $request->getArgument($parameterName),
            ParameterLocation::LOCATION_QUERY => $request->getHttpRequest()->getQueryParams()[$parameterName],
            ParameterLocation::LOCATION_HEADER => $request->getHttpRequest()->getHeader($parameterName),
            ParameterLocation::LOCATION_COOKIE => $request->getHttpRequest()->getCookieParams()[$parameterName],
        };
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
