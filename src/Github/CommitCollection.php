<?php

namespace Zenstruck\Changelog\Github;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class CommitCollection implements \IteratorAggregate, \Countable
{
    /** @var Commit[] */
    private array $commits;

    public function __construct(Repository $repository, array $data)
    {
        $this->commits = \array_map(static fn(array $data) => new Commit($repository, $data), $data);
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
