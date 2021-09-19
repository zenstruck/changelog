<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PendingRelease extends Release
{
    private Version $version;
    private \DateTimeImmutable $publishedAt;

    public function __construct(Version $version, ?Release $previous = null)
    {
        $this->version = $version;
        $this->publishedAt = new \DateTimeImmutable('now');

        parent::__construct([], $previous);
    }

    public function version(): Version
    {
        return $this->version;
    }

    public function publishedAt(): \DateTimeImmutable
    {
        return $this->publishedAt;
    }
}
