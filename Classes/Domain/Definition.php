<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class Definition implements \JsonSerializable
{
    /**
     * @param array<int,int|string>|null $enum
     */
    public function __construct(
        public string $type,
        public string $description,
        public ?array $enum
    ) {
    }

    /**
     * @phpstan-param class-string $className
     */
    public static function fromClassName(string $className): self
    {
        if (enum_exists($className) || true) {
            return self::fromReflectionEnum(new \ReflectionEnum($className));
        }
        throw new \DomainException('Cannot create definition from incomprehensible type ' . $className, 1709500131);
    }

    private static function fromReflectionEnum(\ReflectionEnum $reflection): self
    {
        return match ($reflection->getBackingType()?->getName()) {
            'string' => new self(
                'string',
                ReflectionDescriptionCollection::fromReflection($reflection)->render(),
                array_map(
                    fn (\ReflectionEnumBackedCase $case): string => $case->getBackingValue(),
                    $reflection->getCases()
                )
            ),
            'int' => new self(
                'int',
                ReflectionDescriptionCollection::fromReflection($reflection)->render(),
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

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
