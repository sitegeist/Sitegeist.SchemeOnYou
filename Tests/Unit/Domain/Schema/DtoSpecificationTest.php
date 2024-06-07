<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain\Schema;

use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Schema\IsDataTransferObject;
use Sitegeist\SchemeOnYou\Domain\Schema\IsDataTransferObjectCollection;
use Sitegeist\SchemeOnYou\Domain\Schema\IsSingleValueDataTransferObject;
use Sitegeist\SchemeOnYou\Domain\Schema\IsSupportedInSchema;
use Sitegeist\SchemeOnYou\Tests\Fixtures;

final class DtoSpecificationTest extends TestCase
{
    public static function isSupportedInDataProvider(): \Generator
    {
        yield Fixtures\Composition::class => [
            'typeName' => Fixtures\Composition::class,
            'isSupported' => true,
            'isDTO' => true,
            'isDTC' => false,
            'isSVDTO' => false,
        ];

        yield Fixtures\DayOfWeek::class => [
            'typeName' => Fixtures\DayOfWeek::class,
            'isSupported' => true,
            'isDTO' => true,
            'isDTC' => false,
            'isSVDTO' => true,
        ];

        yield Fixtures\Identifier::class => [
            'typeName' => Fixtures\Identifier::class,
            'isSupported' => true,
            'isDTO' => true,
            'isDTC' => false,
            'isSVDTO' => true,
        ];

        yield Fixtures\IdentifierCollection::class => [
            'typeName' => Fixtures\IdentifierCollection::class,
            'isSupported' => true,
            'isDTO' => false,
            'isDTC' => true,
            'isSVDTO' => false,
        ];

        yield Fixtures\ImportantNumber::class => [
            'typeName' => Fixtures\ImportantNumber::class,
            'isSupported' => true,
            'isDTO' => true,
            'isDTC' => false,
            'isSVDTO' => true,
        ];

        yield Fixtures\Number::class => [
            'typeName' => Fixtures\Number::class,
            'isSupported' => true,
            'isDTO' => true,
            'isDTC' => false,
            'isSVDTO' => true,
        ];

        yield Fixtures\PostalAddress::class => [
            'typeName' => Fixtures\PostalAddress::class,
            'isSupported' => true,
            'isDTO' => true,
            'isDTC' => false,
            'isSVDTO' => false,
        ];

        yield Fixtures\PostalAddressCollection::class => [
            'typeName' => Fixtures\PostalAddressCollection::class,
            'isSupported' => true,
            'isDTO' => false,
            'isDTC' => true,
            'isSVDTO' => false,
        ];

        yield Fixtures\QuantitativeValue::class => [
            'typeName' => Fixtures\QuantitativeValue::class,
            'isSupported' => true,
            'isDTO' => true,
            'isDTC' => false,
            'isSVDTO' => true,
        ];

        yield Fixtures\WeirdThing::class => [
            'typeName' => Fixtures\WeirdThing::class,
            'isSupported' => true,
            'isDTO' => true,
            'isDTC' => false,
            'isSVDTO' => false,
        ];

        // invalid collections

        yield Fixtures\InvalidObjects\CollectionWithTooManyConstructorArguments::class => [
            'typeName' => Fixtures\InvalidObjects\CollectionWithTooManyConstructorArguments::class,
            'isSupported' => false,
            'isDTO' => false,
            'isDTC' => false,
            'isSVDTO' => false,
        ];

        yield Fixtures\InvalidObjects\CollectionWithMoreThanOneProperty::class => [
            'typeName' => Fixtures\InvalidObjects\CollectionWithMoreThanOneProperty::class,
            'isSupported' => false,
            'isDTO' => false,
            'isDTC' => false,
            'isSVDTO' => false,
        ];

        yield Fixtures\InvalidObjects\CollectionThatIsNotReadonly::class => [
            'typeName' => Fixtures\InvalidObjects\CollectionThatIsNotReadonly::class,
            'isSupported' => false,
            'isDTO' => false,
            'isDTC' => false,
            'isSVDTO' => false,
        ];

        // invalid objects

        yield Fixtures\InvalidObjects\ObjectThatIsNotReadonly::class => [
            'typeName' => Fixtures\InvalidObjects\ObjectThatIsNotReadonly::class,
            'isSupported' => false,
            'isDTO' => false,
            'isDTC' => false,
            'isSVDTO' => false,
        ];

        yield Fixtures\InvalidObjects\ObjectThatHasNotSupportedProperties::class => [
            'typeName' => Fixtures\InvalidObjects\ObjectThatHasNotSupportedProperties::class,
            'isSupported' => false,
            'isDTO' => false,
            'isDTC' => false,
            'isSVDTO' => false,
        ];

        yield Fixtures\InvalidObjects\ObjectThatHasNonPromotedConstructorArguments::class => [
            'typeName' => Fixtures\InvalidObjects\ObjectThatHasNonPromotedConstructorArguments::class,
            'isSupported' => false,
            'isDTO' => false,
            'isDTC' => false,
            'isSVDTO' => false,
        ];

        yield Fixtures\InvalidObjects\NonBackedEnum::class => [
            'typeName' => Fixtures\InvalidObjects\NonBackedEnum::class,
            'isSupported' => false,
            'isDTO' => false,
            'isDTC' => false,
            'isSVDTO' => false,
        ];
    }

    /**
     * @dataProvider isSupportedInDataProvider
     * @param class-string $className
     */
    public function testIsSupportedIn(string $className, bool $isSupported, bool $isDTO, bool $isDTC, bool $isSVDTO): void
    {
        $this->assertEquals($isSupported, IsSupportedInSchema::isSatisfiedByClassName($className), sprintf('IsSupportedInSchema should return "%s" for class "%s"', $isSupported ? 'true' : 'false', $className));
        $this->assertEquals($isDTO, IsDataTransferObject::isSatisfiedByClassName($className), sprintf('IsDataTransferObject should return "%s" for class "%s"', $isSupported ? 'true' : 'false', $className));
        $this->assertEquals($isDTC, IsDataTransferObjectCollection::isSatisfiedByClassName($className), sprintf('IsDataTransferObjectCollection should return "%s" for class "%s"', $isSupported ? 'true' : 'false', $className));
        $this->assertEquals($isSVDTO, IsSingleValueDataTransferObject::isSatisfiedByClassName($className), sprintf('IsSingleValueDataTransferObject should return "%s" for class "%s"', $isSupported ? 'true' : 'false', $className));
    }

    public function testFoo(): void
    {
        $this->assertEquals(false, IsDataTransferObject::isSatisfiedByClassName(Fixtures\InvalidObjects\NonBackedEnum::class));
    }
}
