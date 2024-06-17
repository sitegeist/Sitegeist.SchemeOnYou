<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Application;

use GuzzleHttp\Psr7\ServerRequest;
use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Mvc\ActionRequest;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Application\ParameterFactory;
use Sitegeist\SchemeOnYou\Domain\Path\HttpMethod;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Identifier;
use Sitegeist\SchemeOnYou\Tests\Fixtures\IdentifierCollection;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Path\AnotherEndpointQuery;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Path\EndpointQuery;
use Sitegeist\SchemeOnYou\Tests\Controller\PathController;

final class ParameterFactoryTest extends TestCase
{
    /**
     * @param class-string $className
     * @param array<string,mixed> $expectedParameters
     * @dataProvider parameterProvider
     */
    public function testResolveParameters(
        ActionRequest $request,
        string $className,
        string $methodName,
        array $expectedParameters
    ): void {
        Assert::assertEquals(
            $expectedParameters,
            ParameterFactory::resolveParameters($className, $methodName, $request)
        );
    }

    /**
     * @return iterable<string,array<string,mixed>>
     */
    public static function parameterProvider(): iterable
    {
        yield 'withoutParameters' => [
            'request' => ActionRequest::fromHttpRequest(
                new ServerRequest(
                    HttpMethod::METHOD_GET->value,
                    new Uri('https://acme.site')
                )
            ),
            'className' => PathController::class,
            'methodName' => 'nullEndpointAction',
            'expectedParameters' => []
        ];

        yield 'withSingleParameter' => [
            'request' => ActionRequest::fromHttpRequest(
                (new ServerRequest(
                    HttpMethod::METHOD_GET->value,
                    new Uri('https://acme.site/')
                ))->withQueryParams(
                    [
                        'endpointQuery' => '{"language":"de"}'
                    ]
                )
            ),
            'className' => PathController::class,
            'methodName' => 'singleParameterAndResponseEndpointAction',
            'expectedParameters' => [
                'endpointQuery' => new EndpointQuery('de')
            ]
        ];

        yield 'withScalarParameters' => [
            'request' => ActionRequest::fromHttpRequest(
                (new ServerRequest(
                    HttpMethod::METHOD_GET->value,
                    new Uri('https://acme.site/')
                ))->withQueryParams([
                    'name' => 'foo',
                    'number' => 12,
                    'numberWithDecimals' => 33.33,
                    'switch' => 1,
                    'other' => 'suppe'
                ])
            ),
            'className' => PathController::class,
            'methodName' => 'scalarParametersAndResponseEndpointAction',
            'expectedParameters' => [
                'name' => 'foo',
                'number' => 12,
                'numberWithDecimals' => 33.33,
                'switch' => true
            ]
        ];

        yield 'withScalarNullableParameters' => [
            'request' => ActionRequest::fromHttpRequest(
                (new ServerRequest(
                    HttpMethod::METHOD_GET->value,
                    new Uri('https://acme.site/')
                ))->withQueryParams([])
            ),
            'className' => PathController::class,
            'methodName' => 'scalarNullableParameterEndpointAction',
            'expectedParameters' => [
                'message' => null,
                'number' => null,
                'weight' => null,
                'switch' => null,
                'dateTime' => null,
                'dateTimeImmutable' => null,
                'dateInterval' => null
            ]
        ];

        yield 'withScalarParametersAndDefaultValues' => [
            'request' => ActionRequest::fromHttpRequest(
                (new ServerRequest(
                    HttpMethod::METHOD_GET->value,
                    new Uri('https://acme.site/')
                ))->withQueryParams([])
            ),
            'className' => PathController::class,
            'methodName' => 'scalarParameterWithDefaultValuesAction',
            'expectedParameters' => [
                'message' => 'suppe',
                'number' => 42,
                'weight' => 666,
                'switch' => false,
            ]
        ];

        $multipleParametersRequest = ActionRequest::fromHttpRequest(
            (new ServerRequest(
                HttpMethod::METHOD_GET->value,
                new Uri('https://acme.site/de/')
            ))->withQueryParams(
                [
                    'anotherEndpointQuery' => '{"pleaseFail":"true"}'
                ]
            )
        );
        $multipleParametersRequest->setArgument('endpointQuery', ['language' => 'de']);

        yield 'withMultipleParameters' => [
            'request' => $multipleParametersRequest,
            'className' => PathController::class,
            'methodName' => 'multipleParametersAndResponsesEndpointAction',
            'expectedParameters' => [
                'endpointQuery' => new EndpointQuery('de'),
                'anotherEndpointQuery' => new AnotherEndpointQuery(true),
            ]
        ];

        $collectionParametersRequest = ActionRequest::fromHttpRequest(
            (new ServerRequest(
                HttpMethod::METHOD_GET->value,
                new Uri('https://acme.site/?identifierCollection=foo&identifierCollection=bar&identifier=baz')
            ))->withQueryParams([
                'identifierCollection' => '["foo","bar"]',
                'identifier' => 'baz'
            ])
        );

        yield 'withSingleValueObjectsParameterEndpointAction' => [
            'request' => $collectionParametersRequest,
            'className' => PathController::class,
            'methodName' => 'singleValueObjectsParameterEndpointAction',
            'expectedParameters' => [
                'identifierCollection' => new IdentifierCollection(new Identifier('foo'), new Identifier('bar')),
                'identifier' => new Identifier('baz')
            ]
        ];
    }
}
