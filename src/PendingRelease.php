<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PendingRelease extends Release
{
    private Version $version;
    private \DateTimeImmutable $publishedAt;

    public function __construct(Version $version)
    {
        $this->version = $version;
        $this->publishedAt = new \DateTimeImmutable('now');
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
