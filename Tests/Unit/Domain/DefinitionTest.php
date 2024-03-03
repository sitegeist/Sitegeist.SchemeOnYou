<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Unit\Domain;

use Neos\Flow\Annotations as Flow;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Sitegeist\SchemeOnYou\Domain\Definition;
use Sitegeist\SchemeOnYou\Tests\Fixtures\DayOfWeek;
use Sitegeist\SchemeOnYou\Tests\Fixtures\ImportantNumber;

#[Flow\Proxy(false)]
final class DefinitionTest extends TestCase
{
    public function testFromClassName(): void
    {
        Assert::assertEquals(
            new Definition(
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
                ]
            ),
            Definition::fromClassName(DayOfWeek::class)
        );

        Assert::assertEquals(
            new Definition(
                'int',
                'important numbers only',
                [
                    23,
                    42
                ]
            ),
            Definition::fromClassName(ImportantNumber::class)
        );
    }
}
