<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Infrastructure;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ReflectionService;

#[Flow\Scope("singleton")]
class InterfaceImplementationDetector
{
    #[Flow\Inject(name: ReflectionService::class)]
    protected ?ReflectionService $reflectionService = null;

    /**
     * @param class-string $className
     * @return array<class-string>
     */
    public function detect(string $className): array
    {
        if ($this->reflectionService instanceof ReflectionService && interface_exists($className)) {
            return $this->reflectionService->getAllImplementationClassNamesForInterface($className);
        }
        return [];
    }
}
