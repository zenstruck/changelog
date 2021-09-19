<?php

namespace Zenstruck\Changelog;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class GitHubToken
{
    public static function get(): ?string
    {
        return $_SERVER['GITHUB_API_TOKEN'] ?? self::savedToken() ?? $_SERVER['GITHUB_TOKEN'] ?? null;
    }

    public static function save(string $token): void
    {
        (new Filesystem())->dumpFile(
            self::configFile(),
            \json_encode(['github_api_token' => $token], \JSON_THROW_ON_ERROR)
        );
    }

    private static function savedToken(): ?string
    {
        if (!\file_exists($file = self::configFile()) || !\is_readable($file)) {
            return null;
        }

        $array = \json_decode(\file_get_contents($file), true, 512, \JSON_THROW_ON_ERROR);

        return $array['github_api_token'];
    }

    private static function configFile(): string
    {
        return "{$_SERVER['HOME']}/.config/zenstruck/changelog/config.json";
    }
}
