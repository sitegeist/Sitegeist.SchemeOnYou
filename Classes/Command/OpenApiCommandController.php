<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Sitegeist\SchemeOnYou\Domain\OpenApiDocumentRepository;

#[Flow\Scope('singleton')]
final class OpenApiCommandController extends CommandController
{
    #[Flow\InjectConfiguration(path: 'schemaTargetFilePath')]
    protected string $schemaTargetFilePath;

    #[Flow\Inject]
    protected OpenApiDocumentRepository $documentRepository;

    public function renderDocumentCommand(): void
    {
        $schema = $this->documentRepository->findDocument();
        file_put_contents(
            /** @phpstan-ignore-next-line known constant */
            FLOW_PATH_ROOT . $this->schemaTargetFilePath,
            \json_encode(
                $schema,
                JSON_THROW_ON_ERROR + JSON_PRETTY_PRINT + JSON_UNESCAPED_SLASHES + JSON_UNESCAPED_UNICODE
            )
        );
    }
}
