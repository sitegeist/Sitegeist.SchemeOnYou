<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

enum RequestBodyContentType: string implements \JsonSerializable
{
    case CONTENT_TYPE_JSON = 'application/json';
    case CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
