<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Changelog\Configuration;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ConfigureCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('configure')
            ->setDescription('Generate/save GitHub personal access token')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->isInteractive()) {
            throw new \RuntimeException('This command can only be run interactively.');
        }

        $io = new SymfonyStyle($input, $output);
        $token = $io->askHidden(
            'GitHub personal access token (with "repo" scope). Generate: https://github.com/settings/tokens/new?scopes=repo&description=zenstruck%2Fchangelog',
            function($value) {
                if (!$value) {
                    throw new \RuntimeException('A token was not set.');
                }

                return $value;
            }
        );

        (new Configuration())->set(Configuration::GITHUB_API_TOKEN, $token);

        $io->success('Saved personal access token.');

        return 0;
    }
}
