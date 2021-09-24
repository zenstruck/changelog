<?php

namespace Zenstruck\Changelog;

use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Configuration
{
    public const GITHUB_API_TOKEN = 'github_api_token';

    /**
     * @param mixed $default
     */
    public function get(string $option, $default = null)
    {
        return self::config()[$option] ?? $default;
    }

    /**
     * @param mixed $value
     */
    public function set(string $option, $value): void
    {
        $config = self::config();
        $config[$option] = $value;

        (new Filesystem())->dumpFile(self::configFile(), \json_encode($config, \JSON_THROW_ON_ERROR));
    }

    private static function config(): array
    {
        if (\file_exists(self::configFile())) {
            return \json_decode(\file_get_contents(self::configFile()), true, 512, \JSON_THROW_ON_ERROR);
        }

        return [];
    }

    private static function configFile(): string
    {
        return "{$_SERVER['HOME']}/.config/zenstruck/changelog/config.json";
    }
}
