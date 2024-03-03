<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain;

use Neos\Flow\Annotations as Flow;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Definition;
use Sitegeist\SchemeOnYou\Tests\Fixtures\DayOfWeek;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Identifier;
use Sitegeist\SchemeOnYou\Tests\Fixtures\ImportantNumber;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Number;
use Sitegeist\SchemeOnYou\Tests\Fixtures\QuantitativeValue;

#[Flow\Proxy(false)]
final class DefinitionTest extends TestCase
{
    /**
     * @dataProvider definitionProvider
     * @param class-string $className
     */
    public function testFromClassName(string $className, Definition $expectedDefinition): void
    {
        Assert::assertEquals($expectedDefinition, Definition::fromClassName($className));
    }

    public static function definitionProvider(): iterable
    {
        yield 'stringEnum' => [
            'className' => DayOfWeek::class,
            'expectedDefinition' => new Definition(
                'string',
                'see https://schema.org/DayOfWeek',
                [
                    'https://schema.org/Monday',
                    'https://schema.org/Tuesday',
                    'https://schema.org/Wednesday',
                    'https://schema.org/Thursday',
                    'https://schema.org/Friday',
                    'https://schema.org/Saturday',
                    'https://schema.org/Sunday',
                ],
            ),
        ];

        yield 'intEnum' => [
            'className' => ImportantNumber::class,
            'expectedDefinition' => new Definition(
                'int',
                'important numbers only',
                [
                    23,
                    42,
                ],
            ),
        ];

        yield 'stringValueObject' => [
            'className' => Identifier::class,
            'expectedDefinition' => new Definition(
                'string',
                'see https://schema.org/identifier',
            ),
        ];

        yield 'intValueObject' => [
            'className' => QuantitativeValue::class,
            'expectedDefinition' => new Definition(
                'int',
                'see https://schema.org/QuantitativeValue',
            ),
        ];

        yield 'floatValueObject' => [
            'className' => Number::class,
            'expectedDefinition' => new Definition(
                'number',
                'see https://schema.org/Number',
            ),
        ];
    }
}
