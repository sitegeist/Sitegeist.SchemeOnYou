<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;

#[Flow\Scope('singleton')]
final class OpenApiDocumentRepository
{
    /**
     * @var array<string,mixed>
     */
    #[Flow\InjectConfiguration(path: 'rootObject')]
    protected array $rootObjectConfiguration;

    /**
     * @var array<string,array{name:string, classNames: class-string[]}>
     */
    #[Flow\InjectConfiguration(path: 'documents')]
    protected array $documentConfiguration;

    public function __construct(
        private readonly OpenApiDocumentFactory $documentFactory,
    ) {
    }

    public function findDocumentByName(string $name): OpenApiDocument
    {
        if (!array_key_exists($name, $this->documentConfiguration)) {
            throw new \InvalidArgumentException(sprintf('Api spec document "%s" is not configured', $name));
        }

        $documentName = $this->documentConfiguration[$name]['name'] ?? '';
        $documentClassNamePatterns = $this->documentConfiguration[$name]['classNames'];

        return $this->documentFactory->createOpenApiDocumentFromNameAndClassNamePattern(
            $documentName,
            $documentClassNamePatterns,
            $this->rootObjectConfiguration
        );
    }
}
