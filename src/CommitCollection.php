<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CommitCollection extends Collection
{
    /** @var Commit[] */
    private array $commits;
    private string $repository;

    public function __construct(array $commits, string $repository, GitHubApi $api)
    {
        $this->commits = \array_map(static fn(array $commit) => new Commit($commit, $repository, $api), $commits);
        $this->repository = $repository;
    }

    public function repository(): string
    {
        return $this->repository;
    }

    public function reverse(): self
    {
        $clone = $this;
        $clone->commits = \array_reverse($clone->commits);

        return $clone;
    }

    public function withoutMerges(): self
    {
        $clone = $this;
        $clone->commits = \array_filter($clone->commits, static fn(Commit $commit) => !$commit->isMerge());

        return $clone;
    }

    /**
     * @return \Traversable|Commit[]
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->commits);
    }

    public function count(): int
    {
        return \count($this->commits);
    }
}
