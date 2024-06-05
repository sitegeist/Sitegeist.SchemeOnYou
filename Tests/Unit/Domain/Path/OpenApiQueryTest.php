<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Domain\Path;

use PHPUnit\Framework\TestCase;

class OpenApiQueryTest extends TestCase
{
    public static function parseQueryStringWorksDataProvider(): \Generator
    {
        yield 'form-missing' => [
            'queryString' => 'foo=bar&other',
            'parameterName' => 'color',
            'style' => ParameterStyle::STYLE_FORM,
            'explode' => false,
            'expectedValue' => null
        ];

        yield 'form-empty' => [
            'queryString' => 'foo=bar&color&other',
            'parameterName' => 'color',
            'style' => ParameterStyle::STYLE_FORM,
            'explode' => false,
            'expectedValue' => true
        ];

        yield 'form-noexplode-one' => [
            'queryString' => 'foo=bar&color=ignored&color=blue',
            'parameterName' => 'color',
            'style' => ParameterStyle::STYLE_FORM,
            'explode' => false,
            'expectedValue' => 'blue'
        ];

        yield 'form-noexplode-three' => [
            'queryString' => 'foo=bar&color=ignored&color=blue,green,red&other',
            'parameterName' => 'color',
            'style' => ParameterStyle::STYLE_FORM,
            'explode' => false,
            'expectedValue' => ['blue','green','red']
        ];

        yield 'form-explode-one' => [
            'queryString' => 'foo=bar&color=blue&other',
            'parameterName' => 'color',
            'style' => ParameterStyle::STYLE_FORM,
            'explode' => true,
            'expectedValue' => 'blue'
        ];

        yield 'form-explode-three' => [
            'queryString' => 'foo=bar&color=blue&color=green&color=red&other',
            'parameterName' => 'color',
            'style' => ParameterStyle::STYLE_FORM,
            'explode' => true,
            'expectedValue' => ['blue', 'green', 'red']
        ];
    }

    /**
     * @test
     * @dataProvider parseQueryStringWorksDataProvider
     */
    public function parseQueryStringWorks(string $queryString, string $parameterName, ParameterStyle $style, bool $explode, mixed $expectedValue): void
    {
        $parsedQueryString = OpenApiQuery::fromQueryString($queryString);
        $parameterValue = $parsedQueryString->findParameterValue($parameterName, $style, $explode);
        $this->assertEquals($parameterValue, $expectedValue);
    }
}
