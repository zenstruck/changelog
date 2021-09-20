<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PullRequest
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function number(): int
    {
        return $this->data['number'];
    }
}
