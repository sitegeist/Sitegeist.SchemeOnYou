<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Mvc\ActionRequest;

enum RequestBodyContentType: string implements \JsonSerializable
{
    case CONTENT_TYPE_JSON = 'application/json';
    case CONTENT_TYPE_FORM = 'application/x-www-form-urlencoded';

    /**
     * @todo really?
     * @return array<mixed>|int|bool|string|float|null
     */
    public function resolveParameterFromRequest(ActionRequest $request, string $parameterName): array|int|bool|string|float|null
    {
        return match ($this) {
            RequestBodyContentType::CONTENT_TYPE_JSON => (string)$request->getHttpRequest()->getBody(),
            RequestBodyContentType::CONTENT_TYPE_FORM => $request->getArgument($parameterName)
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
            self::CONTENT_TYPE_JSON => match (true) {
                is_string($parameterValue) => \json_decode($parameterValue, true, 512, JSON_THROW_ON_ERROR),
                default => throw new \DomainException('Request body with content type ' . self::CONTENT_TYPE_JSON->value . ' style must be sent as JSON string')
            },
            default => $parameterValue,
        };
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }
}
