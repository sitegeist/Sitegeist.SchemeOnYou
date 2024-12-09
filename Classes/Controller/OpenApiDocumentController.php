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

    #[Flow\InjectConfiguration(path: 'sendCORSHeaders')]
    protected bool $sendCORSHeaders;

    public function renderAction(string $name): string
    {
        $schema = $this->documentRepository->findDocumentByName($name);
        $this->response->setContentType('application\json');
        if ($this->sendCORSHeaders) {
            $this->response->addHttpHeader('Access-Control-Allow-Origin', '*');
        }
        return \json_encode(
            $schema,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
