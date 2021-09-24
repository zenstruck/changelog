<?php

namespace Zenstruck\Changelog\Github;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Api
{
    private HttpClientInterface $http;

    public function __construct(?string $token = null, ?HttpClientInterface $http = null)
    {
        $headers = ['Accept' => 'application/vnd.github.v3+json'];

        if ($token) {
            $headers['Authorization'] = "token {$token}";
        }

        $this->http = ScopingHttpClient::forBaseUri($http ?? HttpClient::create(), 'https://api.github.com/', [
            'headers' => $headers,
        ]);
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
        return $this->http->request($method, $endpoint, $options)->toArray();
    }
}
