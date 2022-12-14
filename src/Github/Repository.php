<?php

/*
 * This file is part of the zenstruck/changelog package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Changelog\Github;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;

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

    public function owner(): string
    {
        return $this->data['owner']['login'];
    }

    public function compare(string $to, ?string $from = null): Comparison
    {
        return new Comparison($this, $to, $from);
    }

    public function commit(string $ref): Commit
    {
        return new Commit($this, $this->api->request('GET', "/repos/{$this}/commits/{$ref}"));
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

    public function saveFile(string $path, string $message, string $content, ?string $branch = null): void
    {
        try {
            $existingFile = $this->getFile($path, $branch);
        } catch (ClientExceptionInterface $e) {
            if (404 !== $e->getResponse()->getStatusCode()) {
                throw $e;
            }

            $existingFile = null;
        }

        $this->api->request('PUT', "/repos/{$this}/contents/{$path}", [
            'json' => \array_filter([
                'content' => \base64_encode($content),
                'message' => $message,
                'branch' => $branch,
                'sha' => $existingFile ? $existingFile->sha() : null,
            ]),
        ]);
    }

    public function getFile(string $path, ?string $branch = null): File
    {
        $ref = $branch ? "?ref={$branch}" : '';

        return new File($this->api->request('GET', "/repos/{$this}/contents/{$path}$ref"));
    }

    public function defaultBranch(): string
    {
        return $this->data['default_branch'];
    }

    public function releases(): ReleaseCollection
    {
        return $this->releases ??= new ReleaseCollection($this, $this->api->request('GET', "/repos/{$this}/releases?per_page=100"));
    }

    public function workflows(): array
    {
        return $this->api->request('GET', "/repos/{$this}/actions/workflows")['workflows'] ?? [];
    }

    public function workflowRuns(): array
    {
        return $this->api->request('GET', "/repos/{$this}/actions/runs")['workflow_runs'] ?? [];
    }

    public function isArchived(): bool
    {
        return $this->data['archived'];
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
