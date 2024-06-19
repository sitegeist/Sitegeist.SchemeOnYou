<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

enum HttpMethod: string implements \JsonSerializable
{
    case METHOD_GET = 'get';
    case METHOD_POST = 'post';
    case METHOD_PUT = 'put';
    case METHOD_DELETE = 'delete';

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
