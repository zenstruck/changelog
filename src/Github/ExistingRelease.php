<?php

namespace Zenstruck\Changelog\Github;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ExistingRelease extends Release
{
    private array $data;

    public function __construct(Repository $repository, array $data)
    {
        parent::__construct($repository);

        $this->data = $data;
    }

    public function name(): string
    {
        return $this->data['name'];
    }

    public function tagName(): string
    {
        return $this->data['tag_name'];
    }

    public function body(): string
    {
        return $this->data['body'];
    }

    public function url(): string
    {
        return $this->data['html_url'];
    }

    public function publishedAt(): \DateTimeImmutable
    {
        return new \DateTimeImmutable($this->data['published_at']);
    }

    public function isPreRelease(): bool
    {
        return $this->data['prerelease'];
    }
}
