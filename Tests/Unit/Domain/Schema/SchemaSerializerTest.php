<?php
declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain\Schema;

use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaDeSerializer;
use Sitegeist\SchemeOnYou\Domain\Schema\SchemaSerializer;
use Sitegeist\SchemeOnYou\Tests\Fixtures;

class SchemaSerializerTest extends TestCase
{
    /**
     * @dataProvider valueSerializationPairs
     * @test
     */
    public function serializesValue(
        mixed $value,
        mixed $serialization
    ): void {
        Assert::assertEquals($serialization, SchemaSerializer::serializeValue($value));
    }

    /**
     * @return iterable<string,mixed>
     */
    public static function valueSerializationPairs(): iterable
    {
        yield 'string stays a string' => ['hello world', '"hello world"'];
        yield 'number stays a number' => [123, '123'];
        yield 'NumberObject is converted' => [
            new Fixtures\Number(value: 123.456),
            <<<EOF
            {
                "value": 123.456
            }
            EOF
        ];
        yield 'DateTime is converted' => [new \DateTime('2010-01-28T15:00:00+02:00'), '"2010-01-28T15:00:00+02:00"'];
        yield 'DateTimeImmutable is converted' => [new \DateTimeImmutable('2010-01-28T15:00:00+02:00'), '"2010-01-28T15:00:00+02:00"'];
        yield 'Int backed Enum is converted' => [Fixtures\ImportantNumber::NUMBER_42, '42'];
        yield 'String backed Enum is converted' => [Fixtures\DayOfWeek::DAY_FRIDAY, '"https:\/\/schema.org\/Friday"'];
        yield 'ValueObject is converted' => [
            new Fixtures\PostalAddress(
                streetAddress: 'Sesame Street 123',
                addressRegion: 'Manhatten',
                addressCountry: 'USA',
                postOfficeBoxNumber: '12345',
            ),
            <<<EOF
            {
                "streetAddress": "Sesame Street 123",
                "addressRegion": "Manhatten",
                "addressCountry": "USA",
                "postOfficeBoxNumber": "12345"
            }
            EOF
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
            <<<EOF
            [
                {
                    "streetAddress": "Sesame Street 123",
                    "addressRegion": "Manhatten",
                    "addressCountry": "USA",
                    "postOfficeBoxNumber": "12345"
                },
                {
                    "streetAddress": "Po\u00dfmoorweg 2",
                    "addressRegion": "Hamburg",
                    "addressCountry": "DE",
                    "postOfficeBoxNumber": "67890"
                }
            ]
            EOF
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
            <<<EOF
            {
                "if": false,
                "what": "dis",
                "howMuch": 23,
                "howMuchPrecisely": 23.42,
                "when": "2010-01-28T15:00:00+02:00",
                "howLong": "P1Y"
            }
            EOF
        ];
    }
}
