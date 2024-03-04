<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain;

use Neos\Flow\Annotations as Flow;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Definition\Definition;
use Sitegeist\SchemeOnYou\Domain\Definition\DefinitionCollection;
use Sitegeist\SchemeOnYou\Domain\Definition\SchemaType;
use Sitegeist\SchemeOnYou\Domain\Metadata\HttpMethod;
use Sitegeist\SchemeOnYou\Domain\Path\Path;
use Sitegeist\SchemeOnYou\Domain\Path\PathParameter;
use Sitegeist\SchemeOnYou\Domain\Path\PathParameterCollection;
use Sitegeist\SchemeOnYou\Domain\Path\PathResponse;
use Sitegeist\SchemeOnYou\Domain\Path\PathResponseCollection;
use Sitegeist\SchemeOnYou\Tests\Fixtures\PathEndpoint;

#[Flow\Proxy(false)]
final class PathTest extends TestCase
{
    /**
     * @dataProvider validMethodsProvider
     * @param class-string $className
     */
    public function testFromClassNameCreatesDefinitionsForValidClasses(
        string $className,
        string $methodName,
        Path $expectedPath
    ): void {
        Assert::assertEquals($expectedPath, Path::fromMethodName($className, $methodName));
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function validMethodsProvider(): iterable
    {
        yield 'basicPath' => [
            'className' => PathEndpoint::class,
            'methodName' => 'endpointMethod',
            'expectedPath' => new Path(
                'my/endpoint',
                HttpMethod::METHOD_GET,
                new PathParameterCollection(
                    new PathParameter(
                        'endpointQuery',
                        true,
                        [
                            '$ref' => '#/definitions/EndpointQuery'
                        ]
                    )
                ),
                new PathResponseCollection(
                    new PathResponse(
                        statusCode: 200,
                        description: 'the query was successful',
                        schema: [
                            '$ref' => '#/definitions/EndpointResponse'
                        ]
                    ),
                    new PathResponse(
                        statusCode: 400,
                        description: 'the query failed',
                        schema: [
                            '$ref' => '#/definitions/EndpointQueryFailed'
                        ]
                    )
                )
            )
        ];
    }
}
