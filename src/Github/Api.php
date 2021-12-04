<?php

namespace Zenstruck\Changelog\Github;

use Psr\Cache\CacheItemInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\ScopingHttpClient;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Api
{
    private HttpClientInterface $http;
    private CacheInterface $cache;

    public function __construct(?string $token = null, ?CacheInterface $cache = null, ?HttpClientInterface $http = null)
    {
        $headers = ['Accept' => 'application/vnd.github.v3+json'];

        if ($token) {
            $headers['Authorization'] = "token {$token}";
        }

        $this->http = ScopingHttpClient::forBaseUri($http ?? HttpClient::create(), 'https://api.github.com/', [
            'headers' => $headers,
        ]);
        $this->cache = $cache ?? new NullAdapter();
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
        if (!\in_array($method = \mb_strtoupper($method), ['GET', 'HEAD', 'OPTIONS'])) {
            return $this->rawRequest($method, $endpoint, $options)->toArray();
        }

        return $this->cache->get($method.$endpoint, function(CacheItemInterface $item) use ($method, $endpoint, $options) {
            $response = $this->rawRequest($method, $endpoint, $options);

            if (\preg_match('#max-age=(\d+)#', $response->getHeaders()['cache-control'][0] ?? '', $matches)) {
                $item->expiresAfter((int) $matches[1]);
            }

            return $response->toArray();
        });
    }

    private function rawRequest(string $method, string $endpoint, array $options = []): ResponseInterface
    {
        return $this->http->request($method, $endpoint, $options);
    }
}
