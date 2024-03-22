<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[OpenApi\Schema('a composition of types')]
#[Flow\Proxy(false)]
final readonly class Composition
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
}
