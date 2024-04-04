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

    /**
     * @param string $name the name of the api document to render
     * @param string $file the name of the spec-file to compare with
     */
    public function verifyCommand(string $name, string $file): void
    {
        $schema = $this->documentRepository->findDocumentByName($name);
        $json = \json_encode(
            $schema,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        ) . PHP_EOL;
        $fileContent = file_get_contents($file);
        if ($json  !== $fileContent) {
            $this->output(sprintf('!!! Content of file "%s" did not match spec document "%s"', $file, $name));
            $this->quit(1);
        }
        $this->outputLine(sprintf('Content of file "%s" matched spec document "%s"', $file, $name));
    }
}
