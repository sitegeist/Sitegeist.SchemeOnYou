<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Sitegeist\SchemeOnYou\Domain\OpenApiDocumentRepository;

class OpenApiDocumentController extends ActionController
{
    #[Flow\Inject]
    protected OpenApiDocumentRepository $documentRepository;

    public function renderAction(string $name): string
    {
        $schema = $this->documentRepository->findDocumentByName($name);
        $this->response->setContentType('application\json');

        return \json_encode(
            $schema,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
