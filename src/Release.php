<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Release
{
    private array $raw;

    public function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    public function __toString(): string
    {
        return $this->version();
    }

    public function version(): Version
    {
        return new Version($this->raw['tag_name']);
    }
}
