<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain\Schema;

use Neos\Http\Factories\UriFactory;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaDenormalizer;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaNormalizer;
use Sitegeist\SchemeOnYou\Tests\Fixtures;

final class SchemaNormalizerTest extends TestCase
{
    public SchemaNormalizer $schemaNormalizer;
    public SchemaDenormalizer $schemaDenormalizer;
    public UriFactory $uriFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uriFactory = new UriFactory();
        $this->schemaNormalizer = new SchemaNormalizer();
        $this->schemaDenormalizer = new SchemaDenormalizer($this->uriFactory);
    }

    /**
     * @dataProvider valueNormalizationPairs
     * @test
     */
    public function normalizeValue(
        string $type,
        mixed $value,
        mixed $normalized
    ): void {
        Assert::assertEquals($normalized, $this->schemaNormalizer->normalizeValue($value));
    }

    /**
     * @dataProvider valueNormalizationPairs
     * @test
     */
    public function denormalizeValue(
        string $type,
        mixed $value,
        mixed $normalized
    ): void {
        Assert::assertEquals($value, $this->schemaDenormalizer->denormalizeValue($normalized, $type));
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function valueNormalizationPairs(): iterable
    {
        // done seperately as dataproviders run before setUp
        $uriFactory = new UriFactory();

        yield 'string' => ['string', 'hello world', 'hello world'];
        yield 'number' => ['int', 123, 123];
        yield 'float' => ['float', 123.456, 123.456];
        yield 'bool true' => ['bool', true, true];
        yield 'bool false' => ['bool', false, false];
        yield 'NumberObject is converted' => [
            Fixtures\Number::class,
            new Fixtures\Number(value: 123.456),
            ["value" => 123.456]
        ];
        yield 'DateTime' => [\DateTime::class, new \DateTime('2010-01-28T15:00:00+02:00'), '2010-01-28T15:00:00+02:00'];
        yield 'DateTimeImmutable' => [\DateTimeImmutable::class, new \DateTimeImmutable('2010-01-28T15:00:00+02:00'), '2010-01-28T15:00:00+02:00'];
        yield 'DateInterval' => [\DateInterval::class, new \DateInterval('P1Y'), 'P1Y'];
        yield 'UriInterface' => [UriInterface::class, $uriFactory->createUri('https://example.com/path?query=foo'), 'https://example.com/path?query=foo'];
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
                where: $uriFactory->createUri('https://www.example.com/path/?query=parameter')
            ),
            [
                "if" => false,
                "what" => "dis",
                "howMuch" => 23,
                "howMuchPrecisely" => 23.42,
                "when" => "2010-01-28T15:00:00+02:00",
                "howLong" => "P1Y",
                "where" => "https://www.example.com/path/?query=parameter"
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
                    where: $uriFactory->createUri('https://www.example.com/path/?query=parameter')
                )
            ),
            [
                'dayOfWeek' => 'https://schema.org/Monday',
                'identifier' => ['value' => 'suppe'],
                'importantNumber' => 23,
                'number' => ['value' => 23.42],
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
                'quantitativeValue' => ['value' => 666],
                'weirdThing' => [
                    "if" => false,
                    "what" => "dis",
                    "howMuch" => 23,
                    "howMuchPrecisely" => 23.42,
                    "when" => "2010-01-28T15:00:00+02:00",
                    "howLong" => "P1Y",
                    "where" => "https://www.example.com/path/?query=parameter"
                ]
            ]
        ];
    }
}
