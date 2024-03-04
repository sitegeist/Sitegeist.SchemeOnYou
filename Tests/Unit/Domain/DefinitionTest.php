<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain;

use Neos\Flow\Annotations as Flow;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Definition\Definition;
use Sitegeist\SchemeOnYou\Domain\Definition\SchemaType;
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
final class DefinitionTest extends TestCase
{
    /**
     * @dataProvider validClassesProvider
     * @param class-string $className
     */
    public function testFromClassNameCreatesDefinitionsForValidClasses(
        string $className,
        Definition $expectedDefinition
    ): void {
        Assert::assertEquals($expectedDefinition, Definition::fromClassName($className));
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function validClassesProvider(): iterable
    {
        $dayOfWeekDefinition = new Definition(
            'DayOfWeek',
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
        );
        yield 'stringEnum' => [
            'className' => DayOfWeek::class,
            'expectedDefinition' => $dayOfWeekDefinition,
        ];

        $importantNumberDefinition = new Definition(
            'ImportantNumber',
            'int',
            'important numbers only',
            [
                23,
                42,
            ],
        );
        yield 'intEnum' => [
            'className' => ImportantNumber::class,
            'expectedDefinition' => $importantNumberDefinition,
        ];

        $identifierDefinition = new Definition(
            'Identifier',
            'string',
            'see https://schema.org/identifier',
        );
        yield 'stringValueObject' => [
            'className' => Identifier::class,
            'expectedDefinition' => $identifierDefinition,
        ];

        $quantitativeValueDefinition = new Definition(
            'QuantitativeValue',
            'int',
            'see https://schema.org/QuantitativeValue',
        );
        yield 'intValueObject' => [
            'className' => QuantitativeValue::class,
            'expectedDefinition' => $quantitativeValueDefinition,
        ];

        $numberDefinition = new Definition(
            'Number',
            'number',
            'see https://schema.org/Number',
        );
        yield 'floatValueObject' => [
            'className' => Number::class,
            'expectedDefinition' => $numberDefinition,
        ];

        $postalAddressDefinition = new Definition(
            name: 'PostalAddress',
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
        );
        yield 'arrayValueObjectWithOptionalAndNullable' => [
            'className' => PostalAddress::class,
            'expectedDefinition' => $postalAddressDefinition,
        ];

        $weirdThingDefinition = new Definition(
            name: 'WeirdThing',
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
                'where' => new SchemaType([
                    'type' => 'string',
                    'format' => 'uri'
                ]),
                'identifier' => new SchemaType([
                    'type' => 'string',
                    'format' => 'uuid'
                ]),
            ],
            required: [
                'if',
                'what',
                'howMuch',
                'howMuchPrecisely',
                'when',
                'howLong',
                'where',
                'identifier',
            ]
        );
        yield 'arrayValueObjectWithDiverselyTypedProperties' => [
            'className' => WeirdThing::class,
            'expectedDefinition' => $weirdThingDefinition,
        ];

        $postalAddressCollectionDefinition = new Definition(
            name: 'PostalAddressCollection',
            type: 'array',
            description: 'a collection of postal addresses, see https://schema.org/PostalAddress',
            items: [
                '$ref' => '#/definitions/PostalAddress',
            ],
        );
        yield 'listValueObject' => [
            'className' => PostalAddressCollection::class,
            'expectedDefinition' => $postalAddressCollectionDefinition,
        ];

        yield 'compositeValueObject' => [
            'className' => Composition::class,
            'expectedDefinition' => new Definition(
                name: 'Composition',
                type: 'object',
                description: 'a composition of types',
                properties: [
                    'dayOfWeek' => new SchemaType([
                        '$ref' => '#/definitions/DayOfWeek'
                    ]),
                    'identifier' => new SchemaType([
                        '$ref' => '#/definitions/Identifier'
                    ]),
                    'importantNumber' => new SchemaType([
                        '$ref' => '#/definitions/ImportantNumber'
                    ]),
                    'number' => new SchemaType([
                        '$ref' => '#/definitions/Number'
                    ]),
                    'postalAddress' => new SchemaType([
                        '$ref' => '#/definitions/PostalAddress'
                    ]),
                    'postalAddressCollection' => new SchemaType([
                        '$ref' => '#/definitions/PostalAddressCollection'
                    ]),
                    'quantitativeValue' => new SchemaType([
                        '$ref' => '#/definitions/QuantitativeValue'
                    ]),
                    'weirdThing' => new SchemaType([
                        '$ref' => '#/definitions/WeirdThing'
                    ]),
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
