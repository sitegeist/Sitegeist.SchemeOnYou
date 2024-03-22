<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Schema;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ClassReflection;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

#[Flow\Scope('singleton')]
class SchemaDenormalizer
{
    use IsTrait;

    public function __construct(
        private readonly UriFactoryInterface $uriFactory
    ) {
    }

    public function denormalizeValue(null|int|bool|string|float|array $value, string $targetType): object|array|int|bool|string|float|null
    {
        return $this->convertValue($value, $targetType);
    }

    private function convertValue(null|int|bool|string|float|array $value, string $targetType): object|array|int|bool|string|float|null
    {
        if ($value === null) {
            return null;
        } elseif ($targetType === 'string') {
            return (string) $value;
        } elseif ($targetType === 'int') {
            return (int) $value;
        } elseif ($targetType === 'float') {
            return (float) $value;
        } elseif ($targetType === 'bool') {
            return (bool) $value;
        } elseif ($targetType === \DateTime::class) {
            return new \DateTime($value);
        } elseif ($targetType === \DateTimeImmutable::class) {
            return new \DateTimeImmutable($value);
        } elseif ($targetType === \DateInterval::class) {
            return new \DateInterval($value);
        } elseif ($targetType === UriInterface::class) {
            return $this->uriFactory->createUri($value);
        } elseif (is_a($targetType, \BackedEnum::class, true)) {
            return $targetType::from($value);
        } elseif (is_array($value) && $this->isCollectionClassName($targetType)) {
            return $this->convertCollection($value, $targetType);
        } elseif (is_array($value) && $this->isValueObjectClassName($targetType)) {
            return $this->convertValueObject($value, $targetType);
        }

        throw new \DomainException('Unsupported type. Only scalar types, BackedEnums, Collections, ValueObjects are supported');
    }

    private function convertCollection(array $value, string $targetType): object
    {
        $reflection = new ClassReflection($targetType);
        $parameterReflection = $reflection->getConstructor()?->getParameters()[0];
        $parameterType = $parameterReflection->getType();
        if (!$parameterType instanceof \ReflectionNamedType) {
            throw new \DomainException('Only named paramerters are supported');
        }
        return new $targetType(
            ...array_map(
                fn($item) => $this->convertValue($item, $parameterType->getName()),
                $value
            )
        );
    }

    private function convertValueObject(array $value, string $targetType): object
    {
        $reflection = new ClassReflection($targetType);
        $parameterReflections = $reflection->getConstructor()?->getParameters();
        $convertedArguments = array_map(
            fn($parameter) => $this->convertValue($value[$parameter->getName()], $parameter->getType()->getName()),
            $parameterReflections
        );

        return new $targetType(...$convertedArguments);
    }
}
