<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Sitegeist\SchemeOnYou\Domain\OpenApiDocumentRepository;

#[Flow\Scope('singleton')]
final class OpenApiDocumentCommandController extends CommandController
{
    #[Flow\Inject]
    protected OpenApiDocumentRepository $documentRepository;

    /**
     * @param string $name the name of the api document to render
     */
    public function renderCommand(string $name): void
    {
        $schema = $this->documentRepository->findDocumentByName($name);
        $this->output->output(
            \json_encode(
                $schema,
                JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
            ) . PHP_EOL
        );
    }
}
