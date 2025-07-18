<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain\Schema;

use Neos\Flow\Reflection\ParameterReflection;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaDenormalizer;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaNormalizer;
use Sitegeist\SchemeOnYou\Tests\Fixtures;
use Sitegeist\SchemeOnYou\Tests\Fixtures\Date;

final class SchemaNormalizerTest extends TestCase
{
    public static function denormalizeObjectsWithOptionalParametersDataProvider(): \Generator
    {
        yield 'min PostalAddress' => [
            Fixtures\PostalAddress::class,
            ['streetAddress' => 'Sesamstraße 42', 'addressRegion' => 'Muppetingen'],
            new Fixtures\PostalAddress(streetAddress: 'Sesamstraße 42', addressRegion: 'Muppetingen')
        ];
        yield 'PostalAddress with missing parameter in between' => [
            Fixtures\PostalAddress::class,
            ['streetAddress' => 'Dämonenweg 23', 'addressRegion' => 'Hölle', 'postOfficeBoxNumber' => '666'],
            new Fixtures\PostalAddress(streetAddress: 'Dämonenweg 23', addressRegion: 'Hölle', postOfficeBoxNumber: '666')
        ];
    }

    /**
     * @dataProvider denormalizeObjectsWithOptionalParametersDataProvider
     * @test
     */
    public function denormalizeObjectsWithOptionalParameters(mixed $type, mixed $data, mixed $expected): void
    {
        Assert::assertEquals($expected, SchemaDenormalizer::denormalizeValue($data, $type));
    }


    /**
     * @dataProvider valueNormalizationPairs
     * @test
     */
    public function normalizeValue(
        string $type,
        mixed $value,
        mixed $normalized,
    ): void {
        Assert::assertEquals($normalized, SchemaNormalizer::normalizeValue($value));
    }

    /**
     * @dataProvider valueNormalizationPairs
     * @test
     */
    public function denormalizeValue(
        string $type,
        mixed $value,
        mixed $normalized,
        ?\ReflectionParameter $reflectionParameter = null
    ): void {
        Assert::assertEquals($value, SchemaDenormalizer::denormalizeValue($normalized, $type, $reflectionParameter));
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function valueNormalizationPairs(): iterable
    {
        yield 'string' => ['string', 'hello world', 'hello world'];
        yield 'number' => ['int', 123, 123];
        yield 'float' => ['float', 123.456, 123.456];
        yield 'bool true' => ['bool', true, true];
        yield 'bool false' => ['bool', false, false];
        yield 'NumberObject is converted' => [
            Fixtures\Number::class,
            new Fixtures\Number(value: 123.456),
            123.456
        ];
        yield 'DateTime' => [\DateTime::class, new \DateTime('2010-01-28T15:00:00+02:00'), '2010-01-28T15:00:00+02:00'];
        yield 'DateTimeImmutable' => [\DateTimeImmutable::class, new \DateTimeImmutable('2010-01-28T15:00:00+02:00'), '2010-01-28T15:00:00+02:00'];
        yield 'DateInterval' => [\DateInterval::class, new \DateInterval('P1Y'), 'P1Y'];
        yield 'Date' => [
            Fixtures\Date::class,
            new Date(new \DateTimeImmutable('2010-01-28T00:00:00' . (new \DateTimeImmutable('2010-01-28T00:00:00'))->format('P'))),
            '2010-01-28',
            (new \ReflectionClass(Date::class))->getConstructor()?->getParameters()[0] ?? null
        ];
        yield 'Int backed Enum' => [Fixtures\ImportantNumber::class, Fixtures\ImportantNumber::NUMBER_42, 42];
        yield 'String backed Enum' => [Fixtures\DayOfWeek::class, Fixtures\DayOfWeek::DAY_FRIDAY, 'https://schema.org/Friday'];
        yield 'PostalAddress' => [
            Fixtures\PostalAddress::class,
            new Fixtures\PostalAddress(
                streetAddress: 'Sesame Street 123',
                addressRegion: 'Manhatten',
                addressCountry: 'USA',
                postOfficeBoxNumber: '12345',
            ),
            [
                'streetAddress' => 'Sesame Street 123',
                'addressRegion' => 'Manhatten',
                'addressCountry' => 'USA',
                'postOfficeBoxNumber' => '12345',
            ]
        ];
        yield 'PostalAddressCollection' => [
            Fixtures\PostalAddressCollection::class,
            new Fixtures\PostalAddressCollection(
                new Fixtures\PostalAddress(
                    streetAddress: 'Sesame Street 123',
                    addressRegion: 'Manhatten',
                    addressCountry: 'USA',
                    postOfficeBoxNumber: '12345',
                ),
                new Fixtures\PostalAddress(
                    streetAddress: 'Poßmoorweg 2',
                    addressRegion: 'Hamburg',
                    addressCountry: 'DE',
                    postOfficeBoxNumber: '67890',
                )
            ),
            [
                [
                    'streetAddress' => 'Sesame Street 123',
                    'addressRegion' => 'Manhatten',
                    'addressCountry' => 'USA',
                    'postOfficeBoxNumber' => '12345',
                ],
                [
                    'streetAddress' => 'Poßmoorweg 2',
                    'addressRegion' => 'Hamburg',
                    'addressCountry' => 'DE',
                    'postOfficeBoxNumber' => '67890',
                ]
            ]
        ];
        yield 'WeirdThing' => [
            Fixtures\WeirdThing::class,
            new Fixtures\WeirdThing(
                if: false,
                what: "dis",
                howMuch: 23,
                howMuchPrecisely: 23.42,
                when: new \DateTimeImmutable('2010-01-28T15:00:00+02:00'),
                howLong: new \DateInterval('P1Y'),
            ),
            [
                "if" => false,
                "what" => "dis",
                "howMuch" => 23,
                "howMuchPrecisely" => 23.42,
                "when" => "2010-01-28T15:00:00+02:00",
                "howLong" => "P1Y",
            ]
        ];
        yield 'Composition' => [
            Fixtures\Composition::class,
            new Fixtures\Composition(
                dayOfWeek: Fixtures\DayOfWeek::DAY_MONDAY,
                identifier: new Fixtures\Identifier('suppe'),
                importantNumber: Fixtures\ImportantNumber::NUMBER_23,
                number: new Fixtures\Number(23.42),
                postalAddress: new Fixtures\PostalAddress(
                    streetAddress: 'Sesame Street 123',
                    addressRegion: 'Manhatten',
                    addressCountry: 'USA',
                    postOfficeBoxNumber: '12345',
                ),
                postalAddressCollection: new Fixtures\PostalAddressCollection(
                    new Fixtures\PostalAddress(
                        streetAddress: 'Sesame Street 123',
                        addressRegion: 'Manhatten',
                        addressCountry: 'USA',
                        postOfficeBoxNumber: '12345',
                    ),
                    new Fixtures\PostalAddress(
                        streetAddress: 'Poßmoorweg 2',
                        addressRegion: 'Hamburg',
                        addressCountry: 'DE',
                        postOfficeBoxNumber: '67890',
                    )
                ),
                quantitativeValue: new Fixtures\QuantitativeValue(666),
                weirdThing: new Fixtures\WeirdThing(
                    if: false,
                    what: "dis",
                    howMuch: 23,
                    howMuchPrecisely: 23.42,
                    when: new \DateTimeImmutable('2010-01-28T15:00:00+02:00'),
                    howLong: new \DateInterval('P1Y'),
                )
            ),
            [
                'dayOfWeek' => 'https://schema.org/Monday',
                'identifier' => 'suppe',
                'importantNumber' => 23,
                'number' => 23.42,
                'postalAddress' => [
                    'streetAddress' => 'Sesame Street 123',
                    'addressRegion' => 'Manhatten',
                    'addressCountry' => 'USA',
                    'postOfficeBoxNumber' => '12345',
                ],
                'postalAddressCollection' => [
                    [
                        'streetAddress' => 'Sesame Street 123',
                        'addressRegion' => 'Manhatten',
                        'addressCountry' => 'USA',
                        'postOfficeBoxNumber' => '12345',
                    ],
                    [
                        'streetAddress' => 'Poßmoorweg 2',
                        'addressRegion' => 'Hamburg',
                        'addressCountry' => 'DE',
                        'postOfficeBoxNumber' => '67890',
                    ]
                ],
                'quantitativeValue' => 666,
                'weirdThing' => [
                    "if" => false,
                    "what" => "dis",
                    "howMuch" => 23,
                    "howMuchPrecisely" => 23.42,
                    "when" => "2010-01-28T15:00:00+02:00",
                    "howLong" => "P1Y",
                ]
            ]
        ];

        yield 'Credentials' => [
            Fixtures\Credentials::class,
            new Fixtures\Credentials(
                username: 'Peter-Klaus Fledermaus',
                password: new Fixtures\Password('secret'),
                expirationDate: new \DateTimeImmutable('2010-01-28T00:00:00' . (new \DateTimeImmutable('2010-01-28T00:00:00'))->format('P'))
            ),
            [
                'username' => 'Peter-Klaus Fledermaus',
                'password' => 'secret',
                'expirationDate' => '2010-01-28',
            ]
        ];
    }

    public static function dateNormalizationAndDenormalizationDateProvider(): \Generator
    {
        yield "default" => [
            '2010-01-28T15:00:00+02:00',
            '2010-01-28T15:00:00+02:00',
            '2010-01-28T15:00:00+02:00'
        ];
        yield "with microseconds" => [
            '2025-07-08T09:37:07.937+02:00',
            '2025-07-08T09:37:07.937+02:00',
            '2025-07-08T09:37:07+02:00'
        ];
    }

    /**
     * @dataProvider dateNormalizationAndDenormalizationDateProvider
     * @test
     */
    public function dateTimeImmutableNormalizationAndDenormalization(string $normalized, string $denormalized, string $renormalized): void
    {
        $expectedDate = new \DateTimeImmutable($denormalized);
        $denormalizedDate = SchemaDenormalizer::denormalizeValue($normalized, \DateTimeImmutable::class);
        $renormalizedDate = SchemaNormalizer::normalizeValue($denormalizedDate);
        Assert::assertEquals($expectedDate, $denormalizedDate);
        Assert::assertEquals($renormalized, $renormalizedDate);
    }

    /**
     * @dataProvider dateNormalizationAndDenormalizationDateProvider
     * @test
     */
    public function dateTimeNormalizationAndDenormalization(string $normalized, string $denormalized, string $renormalized): void
    {

        $expectedDate = new \DateTime($denormalized);
        $denormalizedDate = SchemaDenormalizer::denormalizeValue($normalized, \DateTime::class);
        $renormalizedDate = SchemaNormalizer::normalizeValue($denormalizedDate);
        Assert::assertEquals($expectedDate, $denormalizedDate);
        Assert::assertEquals($renormalized, $renormalizedDate);
    }
}
