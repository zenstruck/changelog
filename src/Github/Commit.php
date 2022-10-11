<?php

namespace Zenstruck\Changelog\Github;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Commit
{
    private Repository $repository;
    private array $data;
    private PullRequestCollection $pullRequests;
    private array $authors;

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

    public function isChangelogUpdate(): bool
    {
        // TODO improve? template? look at files?
        return str_starts_with($this->summary(), '[changelog]');
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

    public function authors(): array
    {
        if (isset($this->authors)) {
            return $this->authors;
        }

        if (!str_contains(\mb_strtolower($this->message()), 'co-authored-by')) {
            return $this->authors = [$this->author()];
        }

        // use graphql to get the users:
        $response = $this->repository->api()->graphQlQuery(<<<EOF
            {
              repository(owner: "{$this->repository->owner()}", name: "{$this->repository->name()}") {
                object(oid: "{$this->sha()}") {
                  ... on Commit {
                    authors(last: 100) {
                      edges {
                        node {
                          user {
                            login
                          }
                          email
                          name
                        }
                      }
                    }
                  }
                }
              }
            }
        EOF);

        return $this->authors = \array_map(
            fn(array $e) => isset($e['node']['user']['login']) ? '@'.$e['node']['user']['login'] : $e['node']['name'] ?? $e['node']['email'],
            $response['data']['repository']['object']['authors']['edges']
        );
    }

    public function format(): string
    {
        $message = "{$this->shortSha()} {$this->summary()}";
        $pr = $this->pullRequests()->first();

        if ($pr && !str_contains($message, $pr = "(#{$pr->number()})")) {
            // add PR link if message doesn't already contain
            $message .= " {$pr}";
        }

        $message = \str_replace(['[feat]', '[fix]'], ['[feature]', '[bug]'], $message);

        return \sprintf('%s by %s', $message, \implode(', ', $this->authors()));
    }

    public function pullRequests(): PullRequestCollection
    {
        return $this->pullRequests ??= $this->repository->pullRequestsFor($this);
    }
}
