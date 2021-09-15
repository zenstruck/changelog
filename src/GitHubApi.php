<?php

namespace Zenstruck\Changelog;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class GitHubApi
{
    private HttpClientInterface $http;

    public function __construct(?string $token = null)
    {
        $headers = ['Accept' => 'application/vnd.github.v3+json'];

        if ($token) {
            $headers['Authorization'] = "token {$token}";
        }

        $this->http = ScopingHttpClient::forBaseUri(HttpClient::create(), 'https://api.github.com/', [
            'headers' => $headers,
        ]);
    }

    public function repository(string $name): Repository
    {
        return new Repository($this->http->request('GET', "/repos/{$name}")->toArray(), $this);
    }

    public function commits(string $repository, Comparison $comparison): CommitCollection
    {
        if (!$comparison->from()) {
            $response = $this->http->request('GET', "/repos/{$repository}/commits?sha={$comparison}")->toArray();

            return new CommitCollection($response, $repository, $this);
        }

        $response = $this->http->request('GET', "/repos/{$repository}/compare/{$comparison}")->toArray();

        if (!isset($response['commits'])) {
            throw new \RuntimeException('Invalid GitHub response.');
        }

        return new CommitCollection($response['commits'], $repository, $this);
    }

    public function releases(string $repository): ReleaseCollection
    {
        $response = $this->http->request('GET', "/repos/{$repository}/releases")->toArray();

        return new ReleaseCollection($response);
    }

    public function pullRequestFor(string $repository, Commit $commit): ?PullRequest
    {
        $response = $this->http->request('GET', "/repos/{$repository}/commits/{$commit->sha()}/pulls", [
            'headers' => ['Accept' => 'application/vnd.github.groot-preview+json'],
        ])->toArray();

        return isset($response[0]) ? new PullRequest($response[0]) : null;
    }
}
