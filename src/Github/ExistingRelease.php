<?php

namespace Zenstruck\Changelog\Github;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ExistingRelease extends Release
{
    private Repository $repository;
    private array $data;

    public function __construct(Repository $repository, array $data)
    {
        $this->repository = $repository;
        $this->data = $data;
    }

    public function compareFrom(?string $from): Comparison
    {
        return $this->repository->compare($this, $from);
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
