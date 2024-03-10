<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\RequestBody;
use Sitegeist\SchemeOnYou\Domain\Metadata\RequestBodyContentType;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiReference;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchema;

/**
 * @see https://swagger.io/docs/specification/describing-request-body/
 */
#[Flow\Proxy(false)]
final readonly class OpenApiRequestBody implements \JsonSerializable
{
    public ?bool $required;

    public function __construct(
        public RequestBodyContentType $contentType,
        public OpenApiSchema|OpenApiReference $schema,
        public ?string $description = null,
        ?bool $required = null,
    ) {
        $this->required = $required;
    }

    public static function fromReflectionParameter(\ReflectionParameter $reflectionParameter): self
    {
        $requestBodyAttribute = RequestBody::fromReflectionParameter($reflectionParameter);
        $reflectionType = $reflectionParameter->getType();
        if (!$reflectionType instanceof \ReflectionNamedType) {
            throw new \DomainException(
                'Request bodies can only be resolved from named parameters',
                1710067045
            );
        }
        $type = $reflectionType->getName();
        if (!class_exists($type)) {
            throw new \DomainException(
                'Request bodies can only be resolved from class parameters, ' . $type . ' given for request body '
                . $reflectionParameter->getDeclaringClass()?->name
                . '::' . $reflectionParameter->getDeclaringFunction()->name
                . '::' . $reflectionParameter->name,
                1710069775
            );
        }

        $reflectionClass = new \ReflectionClass($type);
        if (!$reflectionClass->implementsInterface(RequestParameterContract::class)) {
            throw new \DomainException(
                'Classes used as request bodies must implement the ' . RequestParameterContract::class . ' interface, '
                . $type . ' given for parameter ' . $reflectionParameter->getDeclaringClass()?->name
                . '::' . $reflectionParameter->getDeclaringFunction()->name
                . '::' . $reflectionParameter->name . ' does not',
                1709720053
            );
        }
        $parameterSchema = OpenApiSchema::fromReflectionClass($reflectionClass);
        return new self(
            $requestBodyAttribute->contentType,
            $parameterSchema->toReference(),
            $requestBodyAttribute->description,
            !$reflectionParameter->isDefaultValueAvailable()
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'required' => $this->required,
            'content' => [
                $this->contentType->value => [
                    'schema' => $this->schema
                ]
            ]
        ];
    }
}
