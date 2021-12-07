<?php

namespace Zenstruck\Changelog;

use Symfony\Component\Filesystem\Filesystem;
use Zenstruck\Changelog\Github\PendingRelease;
use Zenstruck\Changelog\Github\Release;
use Zenstruck\Changelog\Github\ReleaseCollection;
use Zenstruck\Changelog\Github\Repository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ChangelogFile
{
    private Repository $repository;
    private string $filename;

    public function __construct(Repository $repository, string $filename)
    {
        $this->repository = $repository;
        $this->filename = $filename;
    }

    /**
     * @return string[]
     */
    public function create(ReleaseCollection $releases): iterable
    {
        if (!\count($releases)) {
            throw new \RuntimeException("No releases available for {$this->repository}.");
        }

        $releases = \iterator_to_array($releases);
        $contents = [];

        yield $contents[] = $this->fileHeader();

        foreach ($releases as $key => $release) {
            foreach ($this->formatRelease($release, $releases[$key + 1] ?? null) as $line) {
                yield $contents[] = $line;
            }
        }

        (new Filesystem())->dumpFile($this->filename, \implode("\n", $contents));
    }

    /**
     * @return string[]
     */
    public function update(Release $to, Release $from): iterable
    {
        $fs = new Filesystem();

        if (!$fs->exists($this->filename)) {
            throw new \RuntimeException("{$this->filename} does not exist, create first.");
        }

        $contents = \file_get_contents($this->filename);
        $updates = [];

        if (!str_contains($contents, $this->fileHeader())) {
            throw new \RuntimeException("{$this->filename} is not in the proper format.");
        }

        if (str_contains($contents, "[{$to}]")) {
            throw new \RuntimeException("{$this->filename} already contains changes for {$to}.");
        }

        yield $updates[] = $this->fileHeader();

        foreach ($this->formatRelease($to, $from) as $line) {
            yield $updates[] = $line;
        }

        $fs->dumpFile($this->filename, \str_replace($this->fileHeader(), \implode("\n", $updates), $contents));
    }

    private function fileHeader(): string
    {
        return '# CHANGELOG';
    }

    /**
     * @return string[]
     */
    private function formatRelease(Release $to, ?Release $from = null): iterable
    {
        yield "\n## [{$to}]({$to->url()})\n";

        $comparison = $to->compareFrom($from);

        if (!$from) {
            yield "{$to->publishedAt()->format('F jS, Y')} - _[Initial Release]({$comparison->url()})_\n";

            return;
        }

        yield "{$to->publishedAt()->format('F jS, Y')} - [{$comparison}]({$comparison->url()})\n";

        $comparison = $to instanceof PendingRelease ? $to->realComparison($from) : $to->compareFrom($from);

        foreach ($comparison->commits() as $commit) {
            yield "* {$commit->format()}";
        }
    }
}
