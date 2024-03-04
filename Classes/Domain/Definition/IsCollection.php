<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Definition;

use Neos\Flow\Annotations as Flow;

#[Flow\Proxy(false)]
final readonly class IsCollection
{
    /**
     * @param \ReflectionClass<object> $reflection
     */
    public static function isSatisfiedByReflectionClass(\ReflectionClass $reflection): bool
    {
        $parameters = $reflection->getConstructor()?->getParameters() ?: [];
        if (count($parameters) === 1) {
            if ($parameters[0]->isVariadic()) {
                return true;
            }
        }
        return false;
    }
}
