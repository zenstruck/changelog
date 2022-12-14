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

    public function withoutBotUpdates(): self
    {
        $clone = $this;
        $clone->commits = \array_filter($clone->commits, static fn(Commit $commit) => !$commit->isBotUpdate());

        return $clone;
    }

    public function withoutChangelogUpdates(): self
    {
        $clone = $this;
        $clone->commits = \array_filter($clone->commits, static fn(Commit $commit) => !$commit->isChangelogUpdate());

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
