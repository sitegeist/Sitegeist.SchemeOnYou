<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Metadata;

use Neos\Flow\Annotations as Flow;

/**
 * @see https://swagger.io/docs/specification/describing-request-body/
 */
#[Flow\Proxy(false)]
#[\Attribute(\Attribute::TARGET_PARAMETER)]
final readonly class RequestBody
{
    public function __construct(
        public RequestBodyContentType $contentType,
        public ?string $description = null,
    ) {
    }

    public static function tryFromReflectionParameter(\ReflectionParameter $reflectionParameter): ?self
    {
        $parameterAttributes = $reflectionParameter->getAttributes(self::class);
        if (count($parameterAttributes) === 1) {
            $arguments = $parameterAttributes[0]->getArguments();

            return new self(
                $arguments['contentType'] ?? $arguments[0],
                $arguments['description'] ?? $arguments[1] ?? null,
            );
        }

        return null;
    }

    public static function fromReflectionParameter(\ReflectionParameter $reflectionParameter): self
    {
        $requestBody = self::tryFromReflectionParameter($reflectionParameter);
        if (!$requestBody) {
            $parameterAttributes = $reflectionParameter->getAttributes(self::class);
            throw new \DomainException(
                'There must be exactly one request body attribute declared in parameter '
                . $reflectionParameter->getDeclaringClass()?->name
                . '::' . $reflectionParameter->getDeclaringFunction()->name
                . '::' . $reflectionParameter->name
                . ', ' . count($parameterAttributes) . ' given',
                1709656717
            );
        }

        return $requestBody;
    }
}
