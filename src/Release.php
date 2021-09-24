<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Release
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __toString(): string
    {
        return $this->data['tag_name'];
    }

    public function url(): string
    {
        return $this->data['html_url'];
    }
}
