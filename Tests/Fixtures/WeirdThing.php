<?php

declare(strict_types=1);

namespace Sitegeist\SchemeOnYou\Tests\Fixtures;

use GuzzleHttp\Psr7\Uri;
use Neos\Flow\Annotations as Flow;
use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Sitegeist\SchemeOnYou\Domain\Metadata\Definition;

#[Definition(description: 'a thing composed of all primitive types')]
#[Flow\Proxy(false)]
final readonly class WeirdThing implements \JsonSerializable
{
    public function __construct(
        public bool $if,
        public string $what,
        public int $howMuch,
        public float $howMuchPrecisely,
        public \DateTimeImmutable $when,
        public \DateInterval $howLong,
        public UriInterface $where,
        public UuidInterface $identifier,
    ) {
    }

    /**
     * @param array<string,mixed> $values
     */
    public static function fromArray(array $values): self
    {
        $when = \DateTimeImmutable::createFromFormat(\DateTimeInterface::RFC3339, $values['when']);
        if (!$when instanceof \DateTimeImmutable) {
            throw new \Exception();
        }
        return new self(
            $values['if'],
            $values['what'],
            $values['howMuch'],
            $values['howMuchPrecisely'],
            $when,
            new \DateInterval($values['howLong']),
            new Uri($values['where']),
            Uuid::fromString($values['where']),
        );
    }

    /**
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'if' => $this->if,
            'what' => $this->what,
            'howMuch' => $this->howMuch,
            'howMuchPrecisely' => $this->howMuchPrecisely,
            'when' => $this->when->format(\DateTimeInterface::RFC3339),
            'howLong' => $this->howLong->format(\DateTimeInterface::ISO8601_EXPANDED),
            'where' => (string)$this->where,
            'identifier' => (string)$this->identifier,
        ];
    }
}
