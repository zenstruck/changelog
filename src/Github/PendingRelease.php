<?php

/*
 * This file is part of the zenstruck/changelog package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Changelog\Github;

use Zenstruck\Changelog\Version;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PendingRelease extends Release
{
    private Version $version;
    private string $target;
    private \DateTimeImmutable $publishedAt;
    private string $body = '';

    public function __construct(Repository $repository, Version $version, ?string $target = null)
    {
        parent::__construct($repository);

        $this->version = $version;
        $this->target = $target ?? $repository->defaultBranch();
        $this->publishedAt = new \DateTimeImmutable();
    }

    public function create(): Release
    {
        return new ExistingRelease($this->repository, $this->repository->api()->request(
            'POST',
            "/repos/{$this->repository}/releases",
            [
                'json' => [
                    'name' => $this->name(),
                    'target_commitish' => $this->target,
                    'tag_name' => $this->tagName(),
                    'prerelease' => $this->isPreRelease(),
                    'body' => $this->body(),
                ],
            ]
        ));
    }

    public function realComparison(?string $from): Comparison
    {
        return $this->repository->compare($this->target, $from);
    }

    public function name(): string
    {
        return $this->version;
    }

    public function tagName(): string
    {
        return $this->version;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function url(): string
    {
        return "https://github.com/{$this->repository}/releases/tag/{$this}";
    }

    public function publishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function isPreRelease(): bool
    {
        return $this->version->isPreRelease();
    }
}
