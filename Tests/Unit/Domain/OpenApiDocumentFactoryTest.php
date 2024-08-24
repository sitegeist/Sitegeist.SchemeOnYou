<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain\Schema;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Routing\Dto\ResolveContext;
use Neos\Flow\Mvc\Routing\Route;
use Neos\Flow\Mvc\Routing\Router;
use Neos\Flow\ObjectManagement\ObjectManager;
use Neos\Flow\Persistence\Doctrine\PersistenceManager;
use Neos\Flow\Reflection\ReflectionService;
use Neos\Http\Factories\UriFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Application\OpenApiController;
use Sitegeist\SchemeOnYou\Domain\OpenApiComponents;
use Sitegeist\SchemeOnYou\Domain\OpenApiDocument;
use Sitegeist\SchemeOnYou\Domain\OpenApiDocumentFactory;
use Sitegeist\SchemeOnYou\Domain\Path\HttpMethod;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiParameter;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiParameterCollection;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiPathCollection;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiPathItem;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiRequestBody;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiResponse;
use Sitegeist\SchemeOnYou\Domain\Path\OpenApiResponses;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterLocation;
use Sitegeist\SchemeOnYou\Domain\Path\ParameterStyle;
use Sitegeist\SchemeOnYou\Domain\Path\PathDefinition;
use Sitegeist\SchemeOnYou\Domain\Path\RequestBodyContentType;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiReference;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchema;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchemaCollection;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaType;
use Sitegeist\SchemeOnYou\Tests\Controller\PathController;

#[Flow\Proxy(false)]
final class OpenApiDocumentFactoryTest extends TestCase
{
    private ?OpenApiDocumentFactory $subject = null;

    /**
     * @var array<string,mixed>
     */
    private array $rootObjectConfiguration = [
        'openapi' => '3.1.0',
        'info' => [],
        'servers' => [],
        'paths' => [],
        'webhooks' => [],
        'components' => [],
        'security' => [],
        'tags' => [],
        'externalDocs' => [],
    ];

    public function setUp(): void
    {
        parent::setUp();
        $mockReflectionService = $this->getMockBuilder(ReflectionService::class)
            ->onlyMethods(['getAllSubClassNamesForClass'])
            ->getMock();
        $mockReflectionService->expects($this->once())
            ->method('getAllSubClassNamesForClass')
            ->with(OpenApiController::class)
            ->willReturn([
                PathController::class
            ]);

        $mockPersistenceManager = $this->getMockBuilder(PersistenceManager::class)
            ->onlyMethods(['convertObjectsToIdentityArrays'])
            ->getMock();
        $mockPersistenceManager->expects($this->any())
            ->method('convertObjectsToIdentityArrays')
            ->willReturnCallback(fn (array $input): array => $input);

        $mockRouter = $this->getMockBuilder(Router::class)
            ->onlyMethods(['getRoutes'])
            ->getMock();
        $mockRouter->expects($this->any())
            ->method('getRoutes')
            ->willReturn([
                $this->createMockRoute(
                    'nullEndpoint',
                    'my-null-endpoint',
                ),
                $this->createMockRoute(
                    'singleParameterAndResponseEndpoint',
                    'my-single-parameter-endpoint'
                ),
                $this->createMockRoute(
                    'scalarParametersAndResponseEndpoint',
                    'my-scalar-parameters-and-response-endpoint'
                ),
                $this->createMockRoute(
                    'scalarParameterEndpoint',
                    'my-scalar-parameter-endpoint'
                ),
                $this->createMockRoute(
                    'scalarNullableParameterEndpoint',
                    'my-nullable-scalar-parameter-endpoint'
                ),
                $this->createMockRoute(
                    'requestBodyAndSingleResponseEndpoint',
                    'my-request-body-and-single-result-endpoint'
                ),
                $this->createMockRoute(
                    'multipleParametersAndResponsesEndpoint',
                    'my-multiple-parameters-and-responses-endpoint'
                ),
                $this->createMockRoute(
                    'singleValueObjectsParameterEndpoint',
                    'single-value-objects-parameter-endpoint'
                ),
            ]);

        $mockObjectManager = $this->getMockBuilder(ObjectManager::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getCaseSensitiveObjectName',
                'getPackageKeyByObjectName',
            ])
            ->getMock();
        $mockObjectManager->expects($this->any())
            ->method('getCaseSensitiveObjectName')
            ->willReturn(PathController::class);
        $mockObjectManager->expects($this->any())
            ->method('getPackageKeyByObjectName')
            ->willReturn('Sitegeist.SchemeOnYou');

        $this->subject = new OpenApiDocumentFactory(
            $mockReflectionService,
            $mockRouter,
            $mockObjectManager,
            new UriFactory()
        );
    }

    private function createMockRoute(
        string $name,
        string $uriPattern
    ): Route {
        $mockRoute = $this->getMockBuilder(Route::class)
            ->onlyMethods(['resolves', 'getUriPattern', 'getHttpMethods'])
            ->getMock();

        $mockRoute->expects($this->any())
            ->method('resolves')
            ->willReturnCallback(fn (ResolveContext $resolveContext): bool => $resolveContext->getRouteValues()['@action'] === $name);
        $mockRoute->expects($this->any())
            ->method('getUriPattern')
            ->willReturn($uriPattern);
        $mockRoute->expects($this->any())
            ->method('getHttpMethods')
            ->willReturn(['GET']);

        return $mockRoute;
    }

    public function testFromClassNameCreatesDefinitionsForValidClasses(): void
    {
        Assert::assertInstanceOf(OpenApiDocumentFactory::class, $this->subject);
        Assert::assertEquals(
            new OpenApiDocument(
                '3.1.0',
                [
                    'title' => 'example'
                ],
                [],
                new OpenApiPathCollection(
                    new OpenApiPathItem(
                        new PathDefinition('/my-null-endpoint'),
                        HttpMethod::METHOD_GET,
                        new OpenApiParameterCollection(),
                        null,
                        new OpenApiResponses(
                            new OpenApiResponse(
                                200,
                                'the query was successful',
                                [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointResponse')
                                    ]
                                ]
                            )
                        )
                    ),
                    new OpenApiPathItem(
                        new PathDefinition('/my-single-parameter-endpoint'),
                        HttpMethod::METHOD_GET,
                        new OpenApiParameterCollection(
                            new OpenApiParameter(
                                name: 'endpointQuery',
                                in: ParameterLocation::LOCATION_QUERY,
                                description: 'the endpoint query',
                                required: true,
                                schema: new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointQuery'),
                                style: ParameterStyle::STYLE_DEEP_OBJECT,
                                content: [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointQuery'),
                                    ]
                                ]
                            )
                        ),
                        null,
                        new OpenApiResponses(
                            new OpenApiResponse(
                                200,
                                'the query was successful',
                                [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointResponse')
                                    ]
                                ]
                            )
                        )
                    ),
                    new OpenApiPathItem(
                        new PathDefinition('/my-scalar-parameters-and-response-endpoint'),
                        HttpMethod::METHOD_GET,
                        new OpenApiParameterCollection(
                            new OpenApiParameter(
                                name: 'name',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'string'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'number',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'integer'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'numberWithDecimals',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'number'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'switch',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'boolean'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                        ),
                        null,
                        new OpenApiResponses(
                            new OpenApiResponse(
                                200,
                                'the query was successful',
                                [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointResponse')
                                    ]
                                ]
                            )
                        )
                    ),
                    new OpenApiPathItem(
                        new PathDefinition('/my-scalar-parameter-endpoint'),
                        HttpMethod::METHOD_GET,
                        new OpenApiParameterCollection(
                            new OpenApiParameter(
                                name: 'message',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'string'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'number',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'integer'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'weight',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'number'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'switch',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'boolean'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'dateTime',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'string', format: 'date-time'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'dateTimeImmutable',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'string', format: 'date-time'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'dateInterval',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'string', format: 'duration'),
                                required: true,
                                style: ParameterStyle::STYLE_FORM
                            ),
                        ),
                        null,
                        new OpenApiResponses(
                            new OpenApiResponse(
                                200,
                                'the query was successful',
                                [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointResponse')
                                    ]
                                ]
                            )
                        )
                    ),
                    new OpenApiPathItem(
                        new PathDefinition('/my-nullable-scalar-parameter-endpoint'),
                        HttpMethod::METHOD_GET,
                        new OpenApiParameterCollection(
                            new OpenApiParameter(
                                name: 'message',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'string'),
                                required: false,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'number',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'integer'),
                                required: false,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'weight',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'number'),
                                required: false,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'switch',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'boolean'),
                                required: false,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'dateTime',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'string', format: 'date-time'),
                                required: false,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'dateTimeImmutable',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'string', format: 'date-time'),
                                required: false,
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'dateInterval',
                                in: ParameterLocation::LOCATION_QUERY,
                                schema: new OpenApiSchema(type: 'string', format: 'duration'),
                                required: false,
                                style: ParameterStyle::STYLE_FORM
                            ),
                        ),
                        null,
                        new OpenApiResponses(
                            new OpenApiResponse(
                                200,
                                'the query was successful',
                                [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointResponse')
                                    ]
                                ]
                            )
                        )
                    ),
                    new OpenApiPathItem(
                        new PathDefinition('/my-request-body-and-single-result-endpoint'),
                        HttpMethod::METHOD_GET,
                        new OpenApiParameterCollection(),
                        new OpenApiRequestBody(
                            contentType: RequestBodyContentType::CONTENT_TYPE_JSON,
                            schema: new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointQuery'),
                            required: true
                        ),
                        new OpenApiResponses(
                            new OpenApiResponse(
                                200,
                                'the query was successful',
                                [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointResponse')
                                    ]
                                ]
                            )
                        )
                    ),
                    new OpenApiPathItem(
                        new PathDefinition('/my-multiple-parameters-and-responses-endpoint'),
                        HttpMethod::METHOD_GET,
                        new OpenApiParameterCollection(
                            new OpenApiParameter(
                                name: 'endpointQuery',
                                in: ParameterLocation::LOCATION_PATH,
                                description: 'the endpoint query',
                                required: true,
                                schema: new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointQuery'),
                                style: ParameterStyle::STYLE_SIMPLE,
                            ),
                            new OpenApiParameter(
                                name: 'anotherEndpointQuery',
                                in: ParameterLocation::LOCATION_QUERY,
                                description: 'another endpoint query',
                                required: true,
                                schema: new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_AnotherEndpointQuery'),
                                content: [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_AnotherEndpointQuery')
                                    ]
                                ],
                                style: ParameterStyle::STYLE_DEEP_OBJECT
                            )
                        ),
                        null,
                        new OpenApiResponses(
                            new OpenApiResponse(
                                200,
                                'the query was successful',
                                [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointResponse')
                                    ]
                                ]
                            ),
                            new OpenApiResponse(
                                400,
                                'the query failed',
                                [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointQueryFailed')
                                    ]
                                ]
                            )
                        )
                    ),
                    new OpenApiPathItem(
                        new PathDefinition('/single-value-objects-parameter-endpoint'),
                        HttpMethod::METHOD_GET,
                        new OpenApiParameterCollection(
                            new OpenApiParameter(
                                name: 'identifier',
                                in: ParameterLocation::LOCATION_QUERY,
                                required: true,
                                description: 'see https://schema.org/identifier',
                                schema: new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Identifier'),
                                style: ParameterStyle::STYLE_FORM
                            ),
                            new OpenApiParameter(
                                name: 'identifierCollection',
                                description: '',
                                in: ParameterLocation::LOCATION_QUERY,
                                required: true,
                                schema: new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_IdentifierCollection'),
                                style: ParameterStyle::STYLE_DEEP_OBJECT,
                                content: [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_IdentifierCollection')
                                    ]
                                ]
                            ),
                        ),
                        null,
                        new OpenApiResponses(
                            new OpenApiResponse(
                                200,
                                'the query was successful',
                                [
                                    'application/json' => [
                                        'schema' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointResponse')
                                    ]
                                ]
                            )
                        )
                    ),
                ),
                [],
                new OpenApiComponents(
                    new OpenApiSchemaCollection(
                        new OpenApiSchema(
                            name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointResponse',
                            type: 'object',
                            description: 'the endpoint response',
                            properties: [
                                'thing' => new SchemaType([
                                    'type' => 'string'
                                ])
                            ],
                            required: [
                            'thing'
                            ]
                        ),
                        new OpenApiSchema(
                            name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointQuery',
                            type: 'object',
                            description: 'the endpoint query',
                            properties: [
                                'language' => new SchemaType([
                                    'type' => 'string'
                                ])
                            ],
                            required: [
                            'language'
                            ]
                        ),
                        new OpenApiSchema(
                            name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_Path_EndpointQueryFailed',
                            type: 'object',
                            description: 'the endpoint query failure response',
                            properties: [
                                'reason' => new SchemaType([
                                    'type' => 'string'
                                ])
                            ],
                            required: [
                            'reason'
                            ]
                        ),
                        new OpenApiSchema(
                            name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_Path_AnotherEndpointQuery',
                            type: 'object',
                            description: 'another endpoint query',
                            properties: [
                                'pleaseFail' => new SchemaType([
                                    'type' => 'boolean'
                                ])
                            ],
                            required: [
                            'pleaseFail'
                            ]
                        ),
                        new OpenApiSchema(
                            name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_Identifier',
                            type: 'string',
                            description: 'see https://schema.org/identifier',
                        ),
                        new OpenApiSchema(
                            name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_IdentifierCollection',
                            type: 'array',
                            items: new OpenApiReference(
                                ref: '#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Identifier'
                            )
                        ),
                    ),
                ),
                [],
                [],
                [],
            ),
            $this->subject->createOpenApiDocumentFromNameAndClassNamePattern(
                'example',
                [
                    PathController::class
                ],
                $this->rootObjectConfiguration,
            )
        );
    }
}
