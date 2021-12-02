<?php

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

    public function latest(): ?Release
    {
        foreach ($this as $release) {
            return $release;
        }

        return null;
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
