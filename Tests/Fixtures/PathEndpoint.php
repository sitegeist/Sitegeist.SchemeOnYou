<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as Scheme;

#[Flow\Proxy(false)]
final readonly class PathEndpoint
{
    #[Scheme\Path(uriPath: 'my/endpoint', httpMethod: Scheme\HttpMethod::METHOD_GET)]
    public function endpointMethod(EndpointQuery $endpointQuery): EndpointResponse|EndpointQueryFailed
    {
        return $endpointQuery->language === ''
            ? new EndpointQueryFailed('Language must not be empty')
            : new EndpointResponse('Hello world');
    }
}
