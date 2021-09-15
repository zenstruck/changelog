<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PullRequest
{
    private array $raw;

    public function __construct(array $raw)
    {
        $this->raw = $raw;
    }

    public function number(): int
    {
        return $this->raw['number'];
    }
}
