<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use Sitegeist\SchemeOnYou\Domain as Scheme;

#[Scheme\Description('see https://schema.org/DayOfWeek')]
enum DayOfWeek: string
{
    case DAY_MONDAY = 'https://schema.org/Monday';
    case DAY_TUESDAY = 'https://schema.org/Tuesday';
    case DAY_WEDNESDAY = 'https://schema.org/Wednesday';
    case DAY_THURSDAY = 'https://schema.org/Thursday';
    case DAY_FRIDAY = 'https://schema.org/Friday';
    case DAY_SATURDAY = 'https://schema.org/Saturday';
    case DAY_SUNDAY = 'https://schema.org/Sunday';
}
