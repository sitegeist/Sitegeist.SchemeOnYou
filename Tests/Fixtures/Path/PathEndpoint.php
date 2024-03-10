<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Path;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterLocation;
use Sitegeist\SchemeOnYou\Domain\Path\PathDefinition;

#[Flow\Proxy(false)]
final readonly class PathEndpoint
{
    #[OpenApi\Path(pathDefinition: new PathDefinition('/my/null-endpoint'), httpMethod: OpenApi\HttpMethod::METHOD_GET)]
    public function nullEndpointMethod(): NullResponse
    {
        return new NullResponse();
    }

    #[OpenApi\Path(
        pathDefinition: new PathDefinition('/my/single-parameter-endpoint'),
        httpMethod: OpenApi\HttpMethod::METHOD_GET
    )]
    public function singleParameterAndResponseEndpointMethod(
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        EndpointQuery $endpointQuery
    ): EndpointResponse {
        return new EndpointResponse('Hello world in language ' . $endpointQuery->language);
    }

    #[OpenApi\Path(
        pathDefinition: new PathDefinition('/my/request-body-endpoint'),
        httpMethod: OpenApi\HttpMethod::METHOD_POST
    )]
    public function requestBodyAndSingleResponseEndpointMethod(
        #[OpenApi\RequestBody(OpenApi\RequestBodyContentType::CONTENT_TYPE_JSON)]
        EndpointQuery $endpointQuery
    ): EndpointResponse {
        return new EndpointResponse('Hello world in language ' . $endpointQuery->language);
    }

    #[OpenApi\Path(
        pathDefinition: new PathDefinition('/my/endpoint/{endpointQuery}'),
        httpMethod: OpenApi\HttpMethod::METHOD_GET
    )]
    public function multipleParametersAndResponsesEndpointMethod(
        #[OpenApi\Parameter(ParameterLocation::LOCATION_PATH)]
        EndpointQuery $endpointQuery,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        AnotherEndpointQuery $anotherEndpointQuery
    ): EndpointResponse|EndpointQueryFailed {
        return $anotherEndpointQuery->pleaseFail
            ? new EndpointQueryFailed('Failure was requested')
            : new EndpointResponse('Hello world in language ' . $endpointQuery->language);
    }
}
