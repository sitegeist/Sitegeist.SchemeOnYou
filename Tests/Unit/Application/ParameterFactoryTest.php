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
                new ServerRequest(
                    HttpMethod::METHOD_GET->value,
                    new Uri('https://acme.site/?endpointQuery=de')
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
                new ServerRequest(
                    HttpMethod::METHOD_GET->value,
                    new Uri('https://acme.site/?name=foo&number=12&numberWithDecimals=33.33&switch=1&other=suppe')
                )
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

        $multipleParametersRequest = ActionRequest::fromHttpRequest(
            new ServerRequest(
                HttpMethod::METHOD_GET->value,
                new Uri('https://acme.site/?endpointQuery=de&anotherEndpointQuery=1')
            )
        );

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
            new ServerRequest(
                HttpMethod::METHOD_GET->value,
                new Uri('https://acme.site/?identifierCollection=foo&identifierCollection=bar&identifier=baz')
            )
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
