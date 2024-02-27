<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain;

use Neos\Flow\Annotations as Flow;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Schema;
use Sitegeist\SchemeOnYou\Tests\Fixtures\PostalAddress;

#[Flow\Proxy(false)]
final class SchemaTest extends TestCase
{
    public function testFromClassName(): void
    {
        Assert::assertEquals(new Schema(), Schema::fromClassName(PostalAddress::class));
    }
}
