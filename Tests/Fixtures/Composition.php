<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as Scheme;

#[Scheme\Definition('a composition of types')]
#[Flow\Proxy(false)]
final readonly class Composition implements \JsonSerializable
{
    public function __construct(
        public DayOfWeek $dayOfWeek,
        public Identifier $identifier,
        public ImportantNumber $importantNumber,
        public Number $number,
        public PostalAddress $postalAddress,
        public PostalAddressCollection $postalAddressCollection,
        public QuantitativeValue $quantitativeValue,
        public WeirdThing $weirdThing,
    ) {
    }

    /**
     * @param array<string,mixed> $values
     */
    public static function fromArray(array $values): self
    {
        return new self(
            DayOfWeek::from($values['dayOfWeek']),
            Identifier::fromString($values['identifier']),
            ImportantNumber::from($values['importantNumber']),
            Number::fromFloat($values['number']),
            PostalAddress::fromArray($values['postalAddress']),
            new PostalAddressCollection(...array_map(
                fn (array $item): PostalAddress => PostalAddress::fromArray($item),
                $values['postalAddressCollection']
            )),
            QuantitativeValue::fromInt($values['quantitativeValue']),
            WeirdThing::fromArray($values['weirdThing'])
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
