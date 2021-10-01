<?php

namespace Zenstruck\Changelog\Github;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Repository
{
    private array $data;
    private Api $api;
    private ReleaseCollection $releases;

    public function __construct(array $data, Api $api)
    {
        $this->data = $data;
        $this->api = $api;
    }

    public function __toString(): string
    {
        return $this->data['full_name'];
    }

    public static function create(string $name, Api $api): self
    {
        return new self($api->request('GET', "/repos/{$name}"), $api);
    }

    /**
     * @return self[]
     */
    public static function forOrganization(string $name, Api $api): array
    {
        return \array_map(
            static fn(array $data) => new self($data, $api),
            $api->request('GET', "/orgs/{$name}/repos?type=public")
        );
    }

    public function name(): string
    {
        return $this->data['name'];
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
        return $this->releases ??= new ReleaseCollection($this, $this->api->request('GET', "/repos/{$this}/releases"));
    }

    public function source(): ?self
    {
        return isset($this->data['source']) ? new self($this->data['source'], $this->api) : null;
    }

    public function api(): Api
    {
        return $this->api;
    }
}
