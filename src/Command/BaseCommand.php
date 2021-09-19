<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Zenstruck\Changelog\GitHubApi;
use Zenstruck\Changelog\Repository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class BaseCommand extends Command
{
    private GitHubApi $api;

    final protected function api(): GitHubApi
    {
        return $this->api ??= new GitHubApi();
    }

    final protected function fetchRepository(?string $name): Repository
    {
        if ($name) {
            return $this->api()->repository($name);
        }

        if (!\file_exists($gitConfigFile = \getcwd().'/.git/config')) {
            // todo recursive look up dir tree (could be in a subdir)
            throw new \RuntimeException('Not able to find git config to guess repository. Use --repository option.');
        }

        $repository = $this->api()->repository(self::parseRepositoryFrom($gitConfigFile));

        // use parent if exists (not a fork)
        return $repository->source() ?? $repository;
    }

    private static function parseRepositoryFrom(string $gitConfigFile): string
    {
        $ini = \parse_ini_file($gitConfigFile, true);

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
