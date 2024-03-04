<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata\Definition as DefinitionAttribute;

#[Flow\Proxy(false)]
final readonly class PathParameterCollection implements \JsonSerializable
{
    /** @var array<PathParameter> */
    private array $items;

    public function __construct(
        PathParameter ...$items
    ) {
        $this->items = $items;
    }

    public static function fromMethodArguments(\ReflectionMethod $reflectionMethod): self
    {
        return new self(...array_map(
            function (\ReflectionParameter $reflectionParameter): PathParameter {
                $reflectionType = $reflectionParameter->getType();
                if (!$reflectionType instanceof \ReflectionNamedType) {
                    throw new \DomainException(
                        'Path parameters can only be resolved from named parameters',
                        1709591991
                    );
                }
                $type = $reflectionType->getName();
                if (!class_exists($type)) {
                    throw new \DomainException(
                        'Path parameters can only be resolved from class parameters',
                        1709592649
                    );
                }
                $definitionAttribute = DefinitionAttribute::fromReflectionClass(new \ReflectionClass($type));

                return new PathParameter(
                    $reflectionParameter->name,
                    !$reflectionParameter->isDefaultValueAvailable(),
                    $definitionAttribute->toReferenceType()
                );
            },
            $reflectionMethod->getParameters()
        ));
    }

    /**
     * @return array<PathParameter>
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }
}
