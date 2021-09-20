<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Formatter
{
    public function changelogHeader(): string
    {
        return "# CHANGELOG\n\n";
    }

    public function release(Release $release, CommitCollection $commits): string
    {
        return <<<EOF
            {$this->releaseHeader($release)}
            {$this->releaseBody($commits)}\n
            [Full Change List]({$release->compareWithPrevious()->url($commits->repository())})
            EOF
        ;
    }

    public function releaseHeader(Release $release): string
    {
        return "## {$release->version()} ({$release->publishedAt()->format('Y-m-d')})\n";
    }

    public function releaseBody(CommitCollection $commits): string
    {
        if ($commits->isEmpty()) {
            return '*(No commits)*';
        }

        return \implode("\n", \array_map(
            fn(Commit $commit) => $this->commit($commit),
            \iterator_to_array($commits->reverse()->withoutMerges())
        ));
    }

    public function commit(Commit $commit): string
    {
        $message = "{$commit->shortSha()} {$commit->summary()}";
        $pr = $commit->pr();

        if ($pr && !str_contains($message, $pr = "(#{$pr->number()})")) {
            // add PR link if message doesn't already contain
            $message .= " {$pr}";
        }

        return \sprintf('%s by %s', $message, \implode(', ', $commit->authors()));
    }
}
