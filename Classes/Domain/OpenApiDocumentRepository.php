<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Reflection\ClassReflection;
use Neos\Flow\Reflection\ReflectionService;
use Sitegeist\SchemeOnYou\Application\OpenApiController;
use Sitegeist\SchemeOnYou\Domain\Metadata\Schema as SchemaAttribute;
use Sitegeist\SchemeOnYou\Domain\Metadata\Path as PathAttribute;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiPathCollection;
use Sitegeist\SchemeOnYou\Domain\Schema\IsCollection;
use Sitegeist\SchemeOnYou\Domain\Schema\IsSupported;
use Sitegeist\SchemeOnYou\Domain\Schema\IsValueObject;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchemaCollection;

#[Flow\Scope('singleton')]
final class OpenApiDocumentRepository
{
    /**
     * @var array<string,mixed>
     */
    #[Flow\InjectConfiguration(path: 'rootObject')]
    protected array $rootObjectConfiguration;

    /**
     * @var array<string,array{name:string, classNames: string[]}>
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

        return $this->documentFactory->createOpenApiDocumentFromNameAncClassNamePattern($documentName, $documentClassNamePatterns, $this->rootObjectConfiguration);
    }
}
