<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain\Path;

use Neos\Flow\Annotations as Flow;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterLocation;
use Sitegeist\SchemeOnYou\Domain\Path\PathDefinition;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiReference;
use Sitegeist\SchemeOnYou\Domain\Metadata\HttpMethod;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiPathItem;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiParameter;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiParameterCollection;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiResponse;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiResponses;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Path\PathEndpoint;

#[Flow\Proxy(false)]
final class OpenApiPathItemTest extends TestCase
{
    /**
     * @dataProvider validMethodsProvider
     * @param class-string $className
     */
    public function testFromClassNameCreatesDefinitionsForValidClasses(
        string $className,
        string $methodName,
        OpenApiPathItem $expectedPath
    ): void {
        Assert::assertEquals($expectedPath, OpenApiPathItem::fromMethodName($className, $methodName));
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function validMethodsProvider(): iterable
    {
        yield 'emptyPath' => [
            'className' => PathEndpoint::class,
            'methodName' => 'nullEndpointMethod',
            'expectedPath' => new OpenApiPathItem(
                new PathDefinition('/my/null-endpoint'),
                HttpMethod::METHOD_GET,
                new OpenApiParameterCollection(),
                new OpenApiResponses(
                    new OpenApiResponse(
                        statusCode: 200,
                        description: 'the query resulted in null',
                        content: [
                            'application/json' => [
                                'schema' => new OpenApiReference('#/components/schemas/NullResponse')
                            ]
                        ]
                    )
                )
            )
        ];

        yield 'singleParameterAndResponsePath' => [
            'className' => PathEndpoint::class,
            'methodName' => 'singleParameterAndResponseEndpointMethod',
            'expectedPath' => new OpenApiPathItem(
                new PathDefinition('/my/single-parameter-endpoint'),
                HttpMethod::METHOD_GET,
                new OpenApiParameterCollection(
                    new OpenApiParameter(
                        'endpointQuery',
                        ParameterLocation::LOCATION_QUERY,
                        'the endpoint query',
                        true,
                        new OpenApiReference('#/components/schemas/EndpointQuery')
                    )
                ),
                new OpenApiResponses(
                    new OpenApiResponse(
                        statusCode: 200,
                        description: 'the query was successful',
                        content: [
                            'application/json' => [
                                'schema' => new OpenApiReference('#/components/schemas/EndpointResponse')
                            ]
                        ]
                    ),
                )
            )
        ];

        yield 'multipleParametersAndResponsesPath' => [
            'className' => PathEndpoint::class,
            'methodName' => 'multipleParametersAndResponsesEndpointMethod',
            'expectedPath' => new OpenApiPathItem(
                new PathDefinition('/my/endpoint/{endpointQuery}'),
                HttpMethod::METHOD_GET,
                new OpenApiParameterCollection(
                    new OpenApiParameter(
                        name: 'endpointQuery',
                        in: ParameterLocation::LOCATION_PATH,
                        description: 'the endpoint query',
                        required: true,
                        schema: new OpenApiReference('#/components/schemas/EndpointQuery')
                    ),
                    new OpenApiParameter(
                        name: 'anotherEndpointQuery',
                        in: ParameterLocation::LOCATION_QUERY,
                        description: 'another endpoint query',
                        required: true,
                        content: [
                            'application/json' => [
                                'schema' => new OpenApiReference('#/components/schemas/AnotherEndpointQuery')
                            ]
                        ],
                        style: 'deepObject'
                    )
                ),
                new OpenApiResponses(
                    new OpenApiResponse(
                        statusCode: 200,
                        description: 'the query was successful',
                        content: [
                            'application/json' => [
                                'schema' => new OpenApiReference('#/components/schemas/EndpointResponse')
                            ]
                        ]
                    ),
                    new OpenApiResponse(
                        statusCode: 400,
                        description: 'the query failed',
                        content: [
                            'application/json' => [
                                'schema' => new OpenApiReference('#/components/schemas/EndpointQueryFailed')
                            ]
                        ]
                    )
                )
            )
        ];
    }
}
