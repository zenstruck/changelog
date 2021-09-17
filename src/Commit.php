<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Commit
{
    private array $raw;
    private string $repository;
    private GitHubApi $api;
    private string $summary;
    private ?PullRequest $pr;

    public function __construct(array $raw, string $repository, GitHubApi $api)
    {
        $this->raw = $raw;
        $this->repository = $repository;
        $this->api = $api;
    }

    public function __toString(): string
    {
        // todo custom formatters?
        $message = "{$this->shortSha()} {$this->summary()}";
        $pr = $this->pr();

        if ($pr && !str_contains($message, $pr = "(#{$pr->number()})")) {
            // add PR link if message doesn't already contain
            $message .= " {$pr}";
        }

        return \sprintf('%s by %s', $message, \implode(', ', $this->authors()));
    }

    public function isMerge(): bool
    {
        // todo improve? currently only looks at the standard message github suggests
        return str_starts_with($this->summary(), 'Merge pull request #');
    }

    public function summary(): string
    {
        return $this->summary ??= \explode("\n", $this->message())[0];
    }

    public function message(): string
    {
        return $this->raw['commit']['message'];
    }

    public function author(): string
    {
        if (isset($this->raw['author']['login'])) {
            return "@{$this->raw['author']['login']}";
        }

        return $this->raw['commit']['author']['name'];
    }

    public function coAuthors(): array
    {
        if (!\preg_match_all('#co-authored-by:(.+)#i', $this->message(), $matches)) {
            return [];
        }

        return \array_map(
            function($value) {
                if (!\preg_match('#<(.+)>#', $value = \trim($value), $matches)) {
                    return $value;
                }

                $email = $matches[1];

                if (\preg_match('#([\w-]+)@users\.noreply\.github\.com#', $email, $matches)) {
                    // parsed login from noreply email
                    return "@{$matches[1]}";
                }

                $login = $this->api->loginForEmail($email);

                return $login ? "@{$login}" : $value;
            },
            $matches[1]
        );
    }

    public function authors(): array
    {
        return \array_merge([$this->author()], $this->coAuthors());
    }

    public function sha(): string
    {
        return $this->raw['sha'];
    }

    public function shortSha(): string
    {
        return \mb_substr($this->sha(), 0, 7);
    }

    public function pr(): ?PullRequest
    {
        if (!isset($this->pr)) {
            $this->pr = $this->api->pullRequestFor($this->repository, $this);
        }

        return $this->pr;
    }
}
