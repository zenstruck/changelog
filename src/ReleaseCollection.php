<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ReleaseCollection extends Collection
{
    /** @var Release[] */
    private array $releases;

    public function __construct(array $releases)
    {
        $this->releases = \array_map(static fn(array $release) => new Release($release), $releases);
    }

    public function nextVersion(string $type): Version
    {
        if ($this->latest()) {
            return $this->latest()->version()->next($type);
        }

        // todo make prefix configurable
        return (new Version('v0.0.0'))->next($type);
    }

    public function latest(): ?Release
    {
        return $this->releases[0] ?? null;
    }

    /**
     * @return \Traversable|Release[]
     */
    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->releases);
    }

    public function count(): int
    {
        return \count($this->releases);
    }
}
