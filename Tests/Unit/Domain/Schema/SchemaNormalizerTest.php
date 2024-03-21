<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain\Schema;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaDenormalizer;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaNormalizer;
use Sitegeist\SchemeOnYou\Tests\Fixtures;

class SchemaNormalizerTest extends TestCase
{
    /**
     * @dataProvider valueNormalizationPairs
     * @test
     */
    public function normalizeValue(
        string $type,
        mixed $value,
        mixed $normalized
    ): void
    {
        Assert::assertEquals($normalized, SchemaNormalizer::normalizeValue($value));
    }

    /**
     * @dataProvider valueNormalizationPairs
     * @test
     */
    public function denormalizeValue(
        string $type,
        mixed $value,
        mixed $normalized
    ): void
    {
        Assert::assertEquals($value, SchemaDenormalizer::denormalizeValue($normalized, $type));
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function valueNormalizationPairs(): iterable
    {
        yield 'string stays a string' => ['string', 'hello world', 'hello world'];
        yield 'number stays a number' => ['int', 123, '123'];
        yield 'NumberObject is converted' => [
            Fixtures\Number::class,
            new Fixtures\Number(value: 123.456),
            ["value" => 123.456]
        ];
        yield 'DateTime is converted' => [\DateTime::class, new \DateTime('2010-01-28T15:00:00+02:00'), '2010-01-28T15:00:00+02:00'];
        yield 'DateTimeImmutable is converted' => [\DateTimeImmutable::class, new \DateTimeImmutable('2010-01-28T15:00:00+02:00'), '2010-01-28T15:00:00+02:00'];
        yield 'Int backed Enum is converted' => [Fixtures\ImportantNumber::class, Fixtures\ImportantNumber::NUMBER_42, 42];
        yield 'String backed Enum is converted' => [Fixtures\DayOfWeek::class, Fixtures\DayOfWeek::DAY_FRIDAY, 'https://schema.org/Friday'];
        yield 'ValueObject is converted' => [
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
        yield 'Collection is converted' => [
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
        yield 'WeirdThing is converted' => [
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
                "howLong" => "P1Y"
            ]
        ];
    }
}
