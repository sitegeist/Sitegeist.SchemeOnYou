<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\Path;

use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterLocation;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterStyle;
use Sitegeist\SchemeOnYou\Domain\Path\PathDefinition;

final readonly class PathEndpoint
{
    #[OpenApi\Path(pathDefinition: new PathDefinition('/my/null-endpoint'), httpMethod: OpenApi\HttpMethod::METHOD_GET)]
    public function nullEndpointMethod(): EndpointResponse
    {
        return new EndpointResponse('acknowledged');
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
        pathDefinition: new PathDefinition('/my/simple-parameter-endpoint'),
        httpMethod: OpenApi\HttpMethod::METHOD_GET
    )]
    public function scalarParametersAndResponseEndpointMethod(
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        string $name,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        int $number,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        float $numberWithDecimals,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        bool $switch,
    ): EndpointResponse {
        return new EndpointResponse('Hello world ' . $name . ' (' . $number . ' ' . $numberWithDecimals . ' ' . ($switch ? 'on' : 'off') . ')');
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
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY, ParameterStyle::STYLE_DEEP_OBJECT)]
        AnotherEndpointQuery $anotherEndpointQuery
    ): EndpointResponse|EndpointQueryFailed {
        return $anotherEndpointQuery->pleaseFail
            ? new EndpointQueryFailed('Failure was requested')
            : new EndpointResponse('Hello world in language ' . $endpointQuery->language);
    }
}
