<?php

namespace Zenstruck\Changelog;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class GitHubApi
{
    private HttpClientInterface $http;

    public function __construct()
    {
        $headers = ['Accept' => 'application/vnd.github.v3+json'];

        if ($token = GitHubToken::get()) {
            $headers['Authorization'] = "token {$token}";
        }

        $this->http = ScopingHttpClient::forBaseUri(HttpClient::create(), 'https://api.github.com/', [
            'headers' => $headers,
        ]);
    }

    public function repository(string $name): Repository
    {
        return new Repository($this->request('GET', "/repos/{$name}"), $this);
    }

    public function commits(Repository $repository, Comparison $comparison): CommitCollection
    {
        if (!$comparison->from()) {
            $response = $this->request('GET', "/repos/{$repository}/commits?sha={$comparison}");

            return new CommitCollection(\array_reverse($response), $repository, $this);
        }

        $response = $this->http->request('GET', "/repos/{$repository}/compare/{$comparison}")->toArray();

        if (!isset($response['commits'])) {
            throw new \RuntimeException('Invalid GitHub response.');
        }

        return new CommitCollection($response['commits'], $repository, $this);
    }

    public function releases(Repository $repository): ReleaseCollection
    {
        $response = $this->request('GET', "/repos/{$repository}/releases");

        return new ReleaseCollection($response);
    }

    public function pullRequestFor(Repository $repository, Commit $commit): ?PullRequest
    {
        $response = $this->request('GET', "/repos/{$repository}/commits/{$commit->sha()}/pulls", [
            'headers' => ['Accept' => 'application/vnd.github.groot-preview+json'],
        ]);

        return isset($response[0]) ? new PullRequest($response[0]) : null;
    }

    public function loginForEmail(string $email): ?string
    {
        $response = $this->request('GET', "/search/users?q={$email} in:email");

        if (!isset($response['items'])) {
            return null;
        }

        foreach ($response['items'] as $item) {
            if ('User' === $item['type']) {
                return $item['login'];
            }
        }

        return null;
    }

    public function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            return $this->http->request($method, $endpoint, $options)->toArray();
        } catch (HttpExceptionInterface $e) {
            if (\in_array($e->getResponse()->getStatusCode(), [401, 403], true)) {
                throw new \RuntimeException('Run "changelog init" to create a github personal access token then run command again.', 0, $e);
            }

            throw $e;
        }
    }
}
