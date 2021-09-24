<?php

namespace Zenstruck\Changelog;

use Zenstruck\Changelog\Github\Api;
use Zenstruck\Changelog\Github\Repository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Factory
{
    private Configuration $configuration;

    public function __construct()
    {
        $this->configuration = new Configuration();
    }

    public function githubApi(): Api
    {
        return new Api($_SERVER['GITHUB_API_TOKEN'] ?? $this->configuration->get(Configuration::GITHUB_API_TOKEN));
    }

    public function repository(?string $name = null): Repository
    {
        $api = $this->githubApi();

        if ($name) {
            return Repository::create($name, $api);
        }

        if (!\file_exists($gitConfigFile = \getcwd().'/.git/config')) {
            // todo recursive look up dir tree (could be in a subdir)
            throw new \RuntimeException('Not able to find git config to guess repository. Use --repository option.');
        }

        $repository = Repository::create(self::parseRepositoryFrom($gitConfigFile), $api);

        return $repository->source() ?? $repository;
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
