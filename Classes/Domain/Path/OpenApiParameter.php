<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\Parameter as ParameterAttribute;
use Sitegeist\SchemeOnYou\Domain\Metadata\Schema as SchemaAttribute;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiReference;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchema;

/**
 * @see https://swagger.io/specification/#parameter-object
 */
#[Flow\Proxy(false)]
final readonly class OpenApiParameter implements \JsonSerializable
{
    public ?bool $required;

    /**
     * @param array<string,mixed>|null $content
     */
    public function __construct(
        public string $name,
        public ParameterLocation $in,
        public ?string $description = null,
        ?bool $required = null,
        public OpenApiSchema|OpenApiReference|null $schema = null,
        public ?array $content = null,
        public ?string $style = null,
    ) {
        $this->required = $in === ParameterLocation::LOCATION_PATH ? true : $required;
    }

    public static function fromReflectionParameter(\ReflectionParameter $reflectionParameter): self
    {
        $reflectionType = $reflectionParameter->getType();
        if (!$reflectionType instanceof \ReflectionNamedType) {
            throw new \DomainException(
                'Path parameters can only be resolved from named parameters',
                1709591991
            );
        }
        $parameterAttribute = ParameterAttribute::fromReflectionParameter($reflectionParameter);
        $type = $reflectionType->getName();
        if (in_array($type, ['int', 'bool', 'string', 'float', \DateTimeImmutable::class, \DateTime::class, \DateInterval::class])) {
            $parameterSchema = OpenApiSchema::fromReflectionParameter($reflectionParameter);
            return new self(
                name: $reflectionParameter->name,
                in: $parameterAttribute->in,
                description: $parameterAttribute->description ?: '',
                required: !$reflectionParameter->allowsNull(),
                schema: $parameterSchema->toReference(),
            );
        }
        if (!class_exists($type)) {
            throw new \DomainException(
                'Path parameters can only be resolved from class parameters, ' . $type . ' given for parameter '
                . $reflectionParameter->getDeclaringClass()?->name
                . '::' . $reflectionParameter->getDeclaringFunction()->name
                . '::' . $reflectionParameter->name,
                1709592649
            );
        }
        $reflectionClass = new \ReflectionClass($type);
        if (!$reflectionClass->implementsInterface(RequestParameterContract::class)) {
            throw new \DomainException(
                'Classes used as path parameters must implement the ' . RequestParameterContract::class . ' interface, '
                . $type . ' given for parameter ' . $reflectionParameter->getDeclaringClass()?->name
                . '::' . $reflectionParameter->getDeclaringFunction()->name
                . '::' . $reflectionParameter->name . ' does not',
                1709720053
            );
        }
        $schemaAttribute = SchemaAttribute::fromReflectionClass($reflectionClass);
        $parameterSchema = OpenApiSchema::fromReflectionClass($reflectionClass);

        return new self(
            name: $reflectionParameter->name,
            in: $parameterAttribute->in,
            description: $parameterAttribute->description ?: $schemaAttribute->description,
            required: !$reflectionParameter->isDefaultValueAvailable(),
            schema: $parameterSchema->type === 'object' ? null : $parameterSchema->toReference(),
            content: $parameterSchema->type === 'object'
                ? [
                    'application/json' => [
                        'schema' => $parameterSchema->toReference()
                    ]
                ]
                : null,
            style: $parameterSchema->type === 'object' ? 'deepObject' : null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(
            get_object_vars($this),
            fn (mixed $value) => $value !== null
        );
    }
}
