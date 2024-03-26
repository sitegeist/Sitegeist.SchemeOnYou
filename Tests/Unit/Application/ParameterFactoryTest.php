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
use Sitegeist\SchemeOnYou\Tests\Fixtures\Path\AnotherEndpointQuery;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Path\EndpointQuery;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Path\PathEndpoint;

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
            'className' => PathEndpoint::class,
            'methodName' => 'nullEndpointMethod',
            'expectedParameters' => []
        ];

        yield 'withSingleParameter' => [
            'request' => ActionRequest::fromHttpRequest(
                (new ServerRequest(
                    HttpMethod::METHOD_GET->value,
                    new Uri('https://acme.site/?endpointQuery=de')
                ))->withQueryParams([
                    'endpointQuery' => [
                        'language' => 'de'
                    ]
                ])
            ),
            'className' => PathEndpoint::class,
            'methodName' => 'singleParameterAndResponseEndpointMethod',
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
            'className' => PathEndpoint::class,
            'methodName' => 'scalarParametersAndResponseEndpointMethod',
            'expectedParameters' => [
                'name' => 'foo',
                'number' => 12,
                'numberWithDecimals' => 33.33,
                'switch' => true
            ]
        ];

        $multipleParametersRequest = ActionRequest::fromHttpRequest(
            (new ServerRequest(
                HttpMethod::METHOD_GET->value,
                new Uri('https://acme.site/?endpointQuery=de')
            ))->withQueryParams([
                'anotherEndpointQuery' => '{"pleaseFail": true}'
            ])
        );
        $multipleParametersRequest->setArgument('endpointQuery', ['language' => 'de']);

        yield 'withMultipleParameters' => [
            'request' => $multipleParametersRequest,
            'className' => PathEndpoint::class,
            'methodName' => 'multipleParametersAndResponsesEndpointMethod',
            'expectedParameters' => [
                'endpointQuery' => new EndpointQuery('de'),
                'anotherEndpointQuery' => new AnotherEndpointQuery(true),
            ]
        ];
    }
}
