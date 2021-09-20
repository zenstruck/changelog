<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Commit
{
    private Repository $repository;
    private array $data;
    private PullRequestCollection $pullRequests;
    private array $coAuthors;

    public function __construct(Repository $repository, array $data)
    {
        $this->repository = $repository;
        $this->data = $data;
    }

    public function isMerge(): bool
    {
        // todo improve? currently only looks at the standard message github suggests
        return str_starts_with($this->summary(), 'Merge pull request #');
    }

    public function summary(): string
    {
        return \explode("\n", $this->message())[0];
    }

    public function message(): string
    {
        return $this->data['commit']['message'];
    }

    public function sha(): string
    {
        return $this->data['sha'];
    }

    public function shortSha(): string
    {
        return \mb_substr($this->sha(), 0, 7);
    }

    public function author(): string
    {
        if (isset($this->data['author']['login'])) {
            return "@{$this->data['author']['login']}";
        }

        return $this->data['commit']['author']['name'];
    }

    public function coAuthors(): array
    {
        if (isset($this->coAuthors)) {
            return $this->coAuthors;
        }

        if (!\preg_match_all('#co-authored-by:(.+)#i', $this->message(), $matches)) {
            return $this->coAuthors = [];
        }

        return $this->coAuthors = \array_map(
            function($value) {
                if (!\preg_match('#<(.+)>#', $value = \trim($value), $matches)) {
                    return $value;
                }

                $email = $matches[1];

                if (\preg_match('#([\w-]+)@users\.noreply\.github\.com#', $email, $matches)) {
                    // parsed login from noreply email
                    return "@{$matches[1]}";
                }

                $login = $this->repository->api()->loginForEmail($email);

                return $login ? "@{$login}" : $value;
            },
            $matches[1]
        );
    }

    public function authors(): array
    {
        return \array_merge([$this->author()], $this->coAuthors());
    }

    public function format(): string
    {
        $message = "{$this->shortSha()} {$this->summary()}";
        $pr = $this->pullRequests()->first();

        if ($pr && !str_contains($message, $pr = "(#{$pr->number()})")) {
            // add PR link if message doesn't already contain
            $message .= " {$pr}";
        }

        return \sprintf('%s by %s', $message, \implode(', ', $this->authors()));
    }

    public function pullRequests(): PullRequestCollection
    {
        return $this->pullRequests ??= $this->repository->pullRequestsFor($this);
    }
}
