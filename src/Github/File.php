<?php

namespace Zenstruck\Changelog\Github;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class File
{
    private array $data;
    private ?string $content;

    public function __construct(array $data)
    {
        if (!isset($data['type']) || 'file' !== $data['type']) {
            throw new \RuntimeException('Not a file.');
        }

        $this->data = $data;
    }

    public function __toString(): string
    {
        return $this->path();
    }

    public function path(): string
    {
        return $this->data['path'];
    }

    public function sha(): string
    {
        return $this->data['sha'];
    }

    public function content(): string
    {
        if (isset($this->content)) {
            return $this->content;
        }

        if ('base64' !== $this->data['encoding']) {
            throw new \RuntimeException('Unable to decode contents.');
        }

        return $this->content = \base64_decode($this->data['content']);
    }
}
