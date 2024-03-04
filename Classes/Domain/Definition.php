<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\Definition as DefinitionMetadata;

#[Flow\Proxy(false)]
final readonly class Definition implements \JsonSerializable
{
    /**
     * @param array<int,int|string>|null $enum
     */
    public function __construct(
        public string $name,
        public string $type,
        public string $description,
        public ?array $enum = null,
        public ?array $properties = null,
        public ?array $required = null,
        public ?array $items = null,
    ) {
    }

    /**
     * @phpstan-param class-string $className
     */
    public static function fromClassName(string $className): self
    {
        if (enum_exists($className)) {
            return self::fromReflectionEnum(new \ReflectionEnum($className));
        } elseif (class_exists($className)) {
            return self::fromReflectionClass(new \ReflectionClass($className));
        }
        throw new \DomainException('Cannot create definition from incomprehensible type ' . $className, 1709500131);
    }

    private static function fromReflectionEnum(\ReflectionEnum $reflection): self
    {
        $definitionMetadata = DefinitionMetadata::fromReflection($reflection);
        return match ($reflection->getBackingType()?->getName()) {
            'string' => new self(
                $definitionMetadata->name ?: $reflection->getShortName(),
                'string',
                $definitionMetadata->description,
                array_map(
                    fn (\ReflectionEnumBackedCase $case): string => $case->getBackingValue(),
                    $reflection->getCases()
                )
            ),
            'int' => new self(
                $definitionMetadata->name ?: $reflection->getShortName(),
                'int',
                $definitionMetadata->description,
                array_map(
                    fn (\ReflectionEnumBackedCase $case): int => $case->getBackingValue(),
                    $reflection->getCases()
                )
            ),
            default => throw new \InvalidArgumentException(
                'Cannot create definition from non-backed enum ' . $reflection->name, 1709499876
            ),
        };
    }

    public static function fromReflectionClass(\ReflectionClass $reflection): self
    {
        if (!in_array('JsonSerializable', $reflection->getInterfaceNames())) {
            throw new \DomainException(
                'Given class ' . $reflection->name . ' does not implement the required \JsonSerializable interface',
                1709503193
            );
        }

        $jsonSerializeReturnType = $reflection->getMethod('jsonSerialize')->getReturnType();
        $returnType = $jsonSerializeReturnType instanceof \ReflectionNamedType ? $jsonSerializeReturnType->getName() : null;
        $allowedReturnTypes = ['string', 'int', 'float', 'array'];
        if (!in_array($returnType, $allowedReturnTypes)) {
            throw new \DomainException(
                'Given class ' . $reflection->name . ' has invalid return type for jsonSerializable, must be one of "'
                . implode('","', $allowedReturnTypes) . '"',
                1709503874
            );
        }
        $definitionMetadata = DefinitionMetadata::fromReflection($reflection);

        $properties = [];
        $required = [];
        $items = null;
        $type = null;
        if ($returnType === 'array') {
            if (count($reflection->getConstructor()->getParameters()) === 1) {
                $onlyParameter = $reflection->getConstructor()->getParameters()[0];
                if ($onlyParameter->isVariadic()) {
                    /** @var \ReflectionNamedType $type */
                    $parameterType = $onlyParameter->getType();
                    $parameterClassName = $parameterType->getName();
                    $type = 'array';
                    $items = [
                        '$ref' => '#/definitions/' . \mb_substr($parameterClassName, \mb_strrpos($parameterClassName, '\\') + 1)
                    ];
                }
            } else {
                foreach ($reflection->getConstructor()->getParameters() as $reflectionParameter) {
                    $properties[$reflectionParameter->name] = [
                        'type' => 'string'
                    ];
                    if (!$reflectionParameter->isDefaultValueAvailable()) {
                        $required[] = $reflectionParameter->name;
                    }
                }
            }
        }

        return new self(
            name: $definitionMetadata->name ?: $reflection->getShortName(),
            type: $type ?: match ($returnType) {
                'string' => 'string',
                'int' => 'int',
                'float' => 'number',
                'array' => 'object'
            },
            description: $definitionMetadata->description,
            properties: ($properties === []) ? null : $properties,
            required: ($required === []) ? null : $required,
            items: $items
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return array_filter(get_object_vars($this));
    }
}
