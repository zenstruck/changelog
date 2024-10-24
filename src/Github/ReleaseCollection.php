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
final class ReleaseCollection implements \IteratorAggregate, \Countable
{
    /** @var Release[] */
    private array $releases;

    public function __construct(Repository $repository, array $data)
    {
        $this->releases = \array_map(static fn(array $item) => new ExistingRelease($repository, $item), $data);
    }

    public function withoutPreReleases(): self
    {
        $clone = $this;
        $clone->releases = \array_filter($clone->releases, static fn(Release $release) => !$release->isPreRelease());

        return $clone;
    }

    public function get(string $name): ?Release
    {
        foreach ($this as $release) {
            if ($name === $release->name() || $name === $release->tagName()) {
                return $release;
            }
        }

        return null;
    }

    public function latest(): ?Release
    {
        foreach ($this as $release) {
            return $release;
        }

        return null;
    }

    public function on(string $target): self
    {
        $clone = $this;
        $clone->releases = \array_filter($clone->releases, static fn(Release $release) => $target === $release->target());

        return $clone;
    }

    /**
     * @return \Traversable|Release[]
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->releases as $release) {
            yield $release;
        }
    }

    public function count(): int
    {
        return \count($this->releases);
    }
}
