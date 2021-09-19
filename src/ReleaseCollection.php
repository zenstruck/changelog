<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ReleaseCollection extends Collection
{
    private array $releases;

    public function __construct(array $releases)
    {
        $this->releases = $releases;
    }

    public function next(string $value): PendingRelease
    {
        return new PendingRelease($this->nextVersion($value), $this->latest());
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
        foreach ($this->releases as $key => $release) {
            // todo improve this?
            yield new Release($release, isset($this->releases[$key + 1]) ? new Release($this->releases[$key + 1]) : null);
        }
    }

    public function count(): int
    {
        return \count($this->releases);
    }

    private function nextVersion(string $value): Version
    {
        if ($this->latest()) {
            return $this->latest()->version()->next($value);
        }

        return Version::first($value);
    }
}
