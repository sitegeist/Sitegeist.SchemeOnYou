<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\View\JsonView;
use Sitegeist\SchemeOnYou\Domain\OpenApiDocumentRepository;

class OpenApiDocumentController extends ActionController
{
    protected $defaultViewObjectName = JsonView::class;

    #[Flow\Inject]
    protected OpenApiDocumentRepository $documentRepository;

    public function renderAction(string $name): string
    {
        $schema = $this->documentRepository->findDocumentByName($name);
        $this->response->setContentType('application\json');
        $this->response->addHttpHeader('Access-Control-Allow-Origin', '*');
        return \json_encode(
            $schema,
            JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );
    }
}
