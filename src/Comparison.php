<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Comparison
{
    private string $to;
    private ?string $from;

    public function __construct(string $to, ?string $from = null)
    {
        $this->to = $to;
        $this->from = $from;
    }

    public function __toString(): string
    {
        return $this->from ? "{$this->from}...{$this->to}" : $this->to;
    }

    public function to(): string
    {
        return $this->to;
    }

    public function from(): ?string
    {
        return $this->from;
    }

    public function url(string $repository): string
    {
        if ($this->from) {
            return "https://github.com/{$repository}/compare/{$this}";
        }

        return "https://github.com/{$repository}/commits/{$this->to}";
    }
}
