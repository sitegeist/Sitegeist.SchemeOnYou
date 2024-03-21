<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain\Schema;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaDeSerializer;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaSerializer;
use Sitegeist\SchemeOnYou\Tests\Fixtures;

class SchemaDeSerializerTest extends TestCase
{
    /**
     * @dataProvider valueSerializationPairs
     * @test
     */
    public function deserializesValue(
        mixed  $value,
        string $targetType,
        mixed  $serialization
    ): void
    {
        Assert::assertEquals($value, SchemaDeSerializer::deSerializeValue($serialization, $targetType));
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function valueSerializationPairs(): iterable
    {
        yield 'string stays a string' => ['hello world', 'string', 'hello world'];
        yield 'number stays a number' => [123, 'int', '123'];
        yield 'NumberObject is converted' => [
            new Fixtures\Number(value: 123.456),
            Fixtures\Number::class,
            ["value" => 123.456]
        ];
        yield 'DateTime is converted' => [new \DateTime('2010-01-28T15:00:00+02:00'), \DateTime::class, '2010-01-28T15:00:00+02:00'];
        yield 'DateTimeImmutable is converted' => [new \DateTimeImmutable('2010-01-28T15:00:00+02:00'), \DateTimeImmutable::class, '2010-01-28T15:00:00+02:00'];
        yield 'Int backed Enum is converted' => [Fixtures\ImportantNumber::NUMBER_42, Fixtures\ImportantNumber::class, 42];
        yield 'String backed Enum is converted' => [Fixtures\DayOfWeek::DAY_FRIDAY, Fixtures\DayOfWeek::class, 'https://schema.org/Friday'];
        yield 'ValueObject is converted' => [
            new Fixtures\PostalAddress(
                streetAddress: 'Sesame Street 123',
                addressRegion: 'Manhatten',
                addressCountry: 'USA',
                postOfficeBoxNumber: '12345',
            ),
            Fixtures\PostalAddress::class,
            [
                'streetAddress' => 'Sesame Street 123',
                'addressRegion' => 'Manhatten',
                'addressCountry' => 'USA',
                'postOfficeBoxNumber' => '12345',
            ]
        ];
        yield 'Collection is converted' => [
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
            Fixtures\PostalAddressCollection::class,
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
            new Fixtures\WeirdThing(
                if: false,
                what: "dis",
                howMuch: 23,
                howMuchPrecisely: 23.42,
                when: new \DateTimeImmutable('2010-01-28T15:00:00+02:00'),
                howLong: new \DateInterval('P1Y'),
            ),
            Fixtures\WeirdThing::class,
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
