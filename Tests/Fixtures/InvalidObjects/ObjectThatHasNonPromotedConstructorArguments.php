<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures\InvalidObjects;

use Neos\Flow\Annotations as Flow;
use Sitegeist\SchemeOnYou\Domain\Metadata as OpenApi;

#[Flow\Proxy(false)]
final readonly class ObjectThatHasNonPromotedConstructorArguments
{
    public string $text;
    public int $num;
    public bool $switch;

    public function __construct(
        string $text,
        int $num,
        bool $switch
    ) {

        $this->text = $text;
        $this->num = $num;
        $this->switch = $switch;
    }
}
