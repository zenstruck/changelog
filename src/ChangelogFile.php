<?php

namespace Zenstruck\Changelog;

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
    private string $content;

    public function __construct(Repository $repository, string $content = '')
    {
        $this->repository = $repository;
        $this->content = $content;
    }

    public static function fromFile(Repository $repository, string $filename): self
    {
        if (!\file_exists($filename)) {
            throw new \RuntimeException("Changelog file \"{$filename}\" does not exist.");
        }

        return new self($repository, \file_get_contents($filename));
    }

    public function saveToFile(string $filename): void
    {
        \file_put_contents($filename, $this->content);
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

        $this->content = \implode("\n", $contents);
    }

    /**
     * @return string[]
     */
    public function update(Release $to, Release $from): iterable
    {
        $updates = [];

        if (!str_contains($this->content, $this->fileHeader())) {
            throw new \RuntimeException('Changelog is not in the proper format.');
        }

        if (str_contains($this->content, "[{$to}]")) {
            throw new \RuntimeException("Changelog already contains changes for {$to}.");
        }

        yield $updates[] = $this->fileHeader();

        foreach ($this->formatRelease($to, $from) as $line) {
            yield $updates[] = $line;
        }

        $this->content = \str_replace($this->fileHeader(), \implode("\n", $updates), $this->content);
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
