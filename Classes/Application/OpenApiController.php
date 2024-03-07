<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Application;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\ActionRequest;
use Neos\Flow\Mvc\ActionResponse;
use Neos\Flow\Mvc\Controller\Arguments;
use Neos\Flow\Mvc\Controller\ControllerContext;
use Neos\Flow\Mvc\Controller\ControllerInterface;
use Neos\Flow\Mvc\Routing\UriBuilder;

#[Flow\Scope('singleton')]
abstract class OpenApiController implements ControllerInterface
{
    protected ActionRequest $request;

    protected ActionResponse $response;

    protected ControllerContext $controllerContext;

    final public function processRequest(ActionRequest $request, ActionResponse $response): void
    {
        $this->request = $request;
        $this->request->setDispatched(true);
        $this->response = $response;
        $this->response->setContentType('application/json');
        $uriBuilder = new UriBuilder();
        $uriBuilder->setRequest($this->request);
        $this->controllerContext = new ControllerContext(
            $this->request,
            $this->response,
            new Arguments([]),
            $uriBuilder
        );

        $actionName = $request->getControllerActionName() . 'Endpoint';
        if (!method_exists($this, $actionName)) {
            throw new \DomainException(
                'Missing action "' . $request->getControllerActionName() . '" in ' . static::class,
                1709678635
            );
        }

        $parameters = ParameterFactory::resolveParameters(get_class($this), $actionName, $this->request);

        $result = $this->$actionName(...$parameters);

        $this->response->setContent(\json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
    }
}
