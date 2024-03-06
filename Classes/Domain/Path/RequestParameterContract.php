<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

interface RequestParameterContract extends \JsonSerializable
{
    /**
     * @param array<string,mixed>|string|int|bool $parameter
     */
    public static function fromRequestParameter(mixed $parameter): static;
}
