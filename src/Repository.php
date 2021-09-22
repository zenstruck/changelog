<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Repository
{
    private array $data;
    private GithubApi $api;
    private ReleaseCollection $releases;

    public function __construct(array $data, GithubApi $api)
    {
        $this->data = $data;
        $this->api = $api;
    }

    public function __toString(): string
    {
        return $this->data['full_name'];
    }

    public static function create(?string $name = null, ?GithubApi $api = null): self
    {
        $api = $api ?? new GithubApi();

        if ($name) {
            return new self($api->request('GET', "/repos/{$name}"), $api);
        }

        if (!\file_exists($gitConfigFile = \getcwd().'/.git/config')) {
            // todo recursive look up dir tree (could be in a subdir)
            throw new \RuntimeException('Not able to find git config to guess repository. Use --repository option.');
        }

        $repository = self::create(self::parseRepositoryFrom($gitConfigFile));

        return $repository->source() ?? $repository;
    }

    public function compare(string $to, ?string $from = null): Comparison
    {
        return new Comparison($this, $to, $from);
    }

    public function commits(Comparison $comparison): CommitCollection
    {
        if (!$comparison->from()) {
            return new CommitCollection(
                $this,
                $this->api->request('GET', "/repos/{$this}/commits?sha={$comparison->to()}")
            );
        }

        return new CommitCollection(
            $this,
            \array_reverse($this->api->request('GET', "/repos/{$this}/compare/{$comparison}")['commits'])
        );
    }

    public function pullRequestsFor(Commit $commit): PullRequestCollection
    {
        return new PullRequestCollection(
            $this->api->request('GET', "/repos/{$this}/commits/{$commit->sha()}/pulls", [
                'headers' => ['Accept' => 'application/vnd.github.groot-preview+json'],
            ])
        );
    }

    public function defaultBranch(): string
    {
        return $this->data['default_branch'];
    }

    public function releases(): ReleaseCollection
    {
        return $this->releases ??= new ReleaseCollection($this->api->request('GET', "/repos/{$this}/releases"));
    }

    public function source(): ?self
    {
        return isset($this->data['source']) ? new self($this->data['source'], $this->api) : null;
    }

    public function api(): GithubApi
    {
        return $this->api;
    }

    private static function parseRepositoryFrom(string $gitConfigFile): string
    {
        $ini = \parse_ini_file($gitConfigFile, true, \INI_SCANNER_RAW);

        foreach ($ini as $section => $items) {
            if (!str_starts_with($section, 'remote')) {
                continue;
            }

            if (!isset($items['url'])) {
                continue;
            }

            if (!\preg_match('#github.com[:/]([\w-]+/[\w-]+)#', $items['url'], $matches)) {
                // not a github repo
                continue;
            }

            return $matches[1];
        }

        throw new \RuntimeException('Unable to find git remote urls');
    }
}
