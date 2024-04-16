<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain\Schema;

use Neos\Flow\Annotations as Flow;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiReference;
use Sitegeist\SchemeOnYou\Domain\Schema\OpenApiSchema;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaType;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Composition;
use Sitegeist\SchemeOnYou\Tests\Fixtures\DayOfWeek;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Identifier;
use Sitegeist\SchemeOnYou\Tests\Fixtures\ImportantNumber;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Number;
use Sitegeist\SchemeOnYou\Tests\Fixtures\PostalAddress;
use Sitegeist\SchemeOnYou\Tests\Fixtures\PostalAddressCollection;
use Sitegeist\SchemeOnYou\Tests\Fixtures\QuantitativeValue;
use Sitegeist\SchemeOnYou\Tests\Fixtures\WeirdThing;

#[Flow\Proxy(false)]
final class OpenApiSchemaTest extends TestCase
{
    /**
     * @dataProvider validClassesProvider
     * @param class-string $className
     */
    public function testFromClassNameCreatesDefinitionsForValidClasses(
        string $className,
        OpenApiSchema $expectedSchema
    ): void {
        Assert::assertEquals($expectedSchema, OpenApiSchema::fromClassName($className));
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function validClassesProvider(): iterable
    {
        yield 'stringEnum' => [
            'className' => DayOfWeek::class,
            'expectedDefinition' => new OpenApiSchema(
                name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_DayOfWeek',
                type: 'string',
                description: 'see https://schema.org/DayOfWeek',
                enum: [
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
            'expectedDefinition' => new OpenApiSchema(
                name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_ImportantNumber',
                type: 'integer',
                description: 'important numbers only',
                enum: [
                    23,
                    42,
                ],
            ),
        ];

        yield 'stringValueObject' => [
            'className' => Identifier::class,
            'expectedDefinition' => new OpenApiSchema(
                name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_Identifier',
                type: 'string',
                description: 'see https://schema.org/identifier',
            ),
        ];

        yield 'intValueObject' => [
            'className' => QuantitativeValue::class,
            'expectedDefinition' => new OpenApiSchema(
                name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_QuantitativeValue',
                type: 'integer',
                description: 'see https://schema.org/QuantitativeValue',
            ),
        ];

        yield 'floatValueObject' => [
            'className' => Number::class,
            'expectedDefinition' => new OpenApiSchema(
                name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_Number',
                type: 'number',
                description: 'see https://schema.org/Number',
            ),
        ];

        yield 'arrayValueObjectWithOptionalAndNullable' => [
            'className' => PostalAddress::class,
            'expectedDefinition' => new OpenApiSchema(
                name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_PostalAddress',
                type: 'object',
                description: 'see https://schema.org/PostalAddress',
                properties: [
                    'streetAddress' => new SchemaType([
                        'type' => 'string',
                    ]),
                    'addressRegion' => new SchemaType([
                        'oneOf' => [
                            [
                                'type' => 'string'
                            ],
                            [
                                'type' => 'null'
                            ]
                        ],
                    ]),
                    'addressCountry' => new SchemaType([
                        'oneOf' => [
                            [
                                'type' => 'string'
                            ],
                            [
                                'type' => 'null'
                            ]
                        ],
                    ]),
                    'postOfficeBoxNumber' => new SchemaType([
                        'oneOf' => [
                            [
                                'type' => 'string'
                            ],
                            [
                                'type' => 'null'
                            ]
                        ],
                    ]),
                ],
                required: [
                    'streetAddress',
                    'addressRegion',
                ],
            ),
        ];

        yield 'arrayValueObjectWithDiverselyTypedProperties' => [
            'className' => WeirdThing::class,
            'expectedDefinition' => new OpenApiSchema(
                name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_WeirdThing',
                type: 'object',
                description: 'a thing composed of all primitive types',
                properties: [
                    'if' => new SchemaType([
                        'type' => 'boolean',
                    ]),
                    'what' => new SchemaType([
                        'type' => 'string',
                    ]),
                    'howMuch' => new SchemaType([
                        'type' => 'integer',
                    ]),
                    'howMuchPrecisely' => new SchemaType([
                        'type' => 'number',
                    ]),
                    'when' => new SchemaType([
                        'type' => 'string',
                        'format' => 'date-time'
                    ]),
                    'howLong' => new SchemaType([
                        'type' => 'string',
                        'format' => 'duration'
                    ]),
                ],
                required: [
                    'if',
                    'what',
                    'howMuch',
                    'howMuchPrecisely',
                    'when',
                    'howLong',
                ]
            ),
        ];

        yield 'listValueObject' => [
            'className' => PostalAddressCollection::class,
            'expectedDefinition' => new OpenApiSchema(
                name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_PostalAddressCollection',
                type: 'array',
                description: 'a collection of postal addresses, see https://schema.org/PostalAddress',
                items: new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_PostalAddress'),
            ),
        ];

        yield 'compositeValueObject' => [
            'className' => Composition::class,
            'expectedDefinition' => new OpenApiSchema(
                name: 'Sitegeist_SchemeOnYou_Tests_Fixtures_Composition',
                type: 'object',
                description: 'a composition of types',
                properties: [
                    'dayOfWeek' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_DayOfWeek'),
                    'identifier' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Identifier'),
                    'importantNumber' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_ImportantNumber'),
                    'number' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_Number'),
                    'postalAddress' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_PostalAddress'),
                    'postalAddressCollection' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_PostalAddressCollection'),
                    'quantitativeValue' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_QuantitativeValue'),
                    'weirdThing' => new OpenApiReference('#/components/schemas/Sitegeist_SchemeOnYou_Tests_Fixtures_WeirdThing'),
                ],
                required: [
                    'dayOfWeek',
                    'identifier',
                    'importantNumber',
                    'number',
                    'postalAddress',
                    'postalAddressCollection',
                    'quantitativeValue',
                    'weirdThing',
                ]
            )
        ];
    }
}
