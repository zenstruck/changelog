<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class Release
{
    private array $raw;
    private ?Release $previous;

    public function __construct(array $raw, ?self $previous = null)
    {
        $this->raw = $raw;
        $this->previous = $previous;
    }

    public function __toString(): string
    {
        return $this->version();
    }

    public function version(): Version
    {
        return new Version($this->raw['tag_name']);
    }

    public function publishedAt(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->raw['published_at']);
    }

    public function previous(): ?self
    {
        return $this->previous;
    }

    public function compareWithPrevious(): Comparison
    {
        return new Comparison($this->previous(), $this);
    }
}
