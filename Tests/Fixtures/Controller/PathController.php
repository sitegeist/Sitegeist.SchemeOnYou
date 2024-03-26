<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Controller;

use Sitegeist\SchemeOnYou\Application\OpenApiController;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterLocation;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterStyle;
use Sitegeist\SchemeOnYou\Domain\Path\RequestBodyContentType;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Path\AnotherEndpointQuery;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Path\EndpointQuery;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Path\EndpointQueryFailed;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Path\EndpointResponse;

final class PathController extends OpenApiController
{
    public function nullEndpointAction(): EndpointResponse
    {
        return new EndpointResponse('acknowledged');
    }

    public function singleParameterAndResponseEndpointAction(
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        EndpointQuery $endpointQuery
    ): EndpointResponse {
        return new EndpointResponse('Hello world in language ' . $endpointQuery->language);
    }

    public function scalarParametersAndResponseEndpointAction(
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

    public function scalarParameterEndpointAction(
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        string $message,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        int $number,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        float $weight,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        bool $switch,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        \DateTime $dateTime,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        \DateTime $dateTimeImmutable,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        \DateInterval $dateInterval,
    ): EndpointResponse {
        return new EndpointResponse('acknowledged');
    }

    public function scalarNullableParameterEndpointAction(
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        ?string $message = null,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        ?int $number = null,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        ?float $weight = null,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        ?bool $switch = null,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        ?\DateTime $dateTime = null,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        ?\DateTime $dateTimeImmutable = null,
        #[OpenApi\Parameter(ParameterLocation::LOCATION_QUERY)]
        ?\DateInterval $dateInterval = null,
    ): EndpointResponse {
        return new EndpointResponse('acknowledged');
    }

    public function requestBodyAndSingleResponseEndpointAction(
        #[OpenApi\RequestBody(RequestBodyContentType::CONTENT_TYPE_JSON)]
        EndpointQuery $endpointQuery
    ): EndpointResponse {
        return new EndpointResponse('Hello world in language ' . $endpointQuery->language);
    }

    public function multipleParametersAndResponsesEndpointAction(
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
