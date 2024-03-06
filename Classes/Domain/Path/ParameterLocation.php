<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

enum ParameterLocation: string implements \JsonSerializable
{
    case LOCATION_PATH = 'path';
    case LOCATION_QUERY = 'query';
    case LOCATION_HEADER = 'header';
    case LOCATION_COOKIE = 'cookie';

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
