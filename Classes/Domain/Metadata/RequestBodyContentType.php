<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Mvc\ActionRequest;

enum RequestBodyContentType: string implements \JsonSerializable
{
    case CONTENT_TYPE_JSON = 'application/json';
    case CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    /**
     * @todo really?
     * @return object|array<mixed>|int|bool|string|float|null
     */
    public function resolveParameterFromRequest(ActionRequest $request, string $parameterName): object|array|int|bool|string|float|null
    {
        return match ($this) {
            RequestBodyContentType::CONTENT_TYPE_JSON => (string)$request->getHttpRequest()->getBody(),
            RequestBodyContentType::CONTENT_TYPE_FORM => $request->getArgument($parameterName)
        };
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
