<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Formatter
{
    public function changelogHeader(): string
    {
        return '# CHANGELOG';
    }

    public function releaseHeader(Release $release): string
    {
        return "## {$release->version()} ({$release->publishedAt()->format('Y-m-d')})";
    }

    public function releaseBody(CommitCollection $commits, ?Comparison $comparison = null): string
    {
        if ($commits->isEmpty()) {
            return '*(No commits)*';
        }

        $ret = \implode("\n", \array_map(
            fn(Commit $commit) => $this->commit($commit),
            \iterator_to_array($commits->reverse()->withoutMerges())
        ));

        if ($comparison) {
            $ret .= "\n\n[Full Change List]({$comparison->url($commits->repository())}";
        }

        return $ret;
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
