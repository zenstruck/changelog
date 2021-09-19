<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Changelog\GitHubToken;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class InitCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('init')
            ->setDescription('Generate/save GitHub personal access token')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$input->isInteractive()) {
            throw new \RuntimeException('This command can only be run interactively.');
        }

        $io = new SymfonyStyle($input, $output);
        $token = $io->askHidden('GitHub personal access token (with "repo" scope). Generate: https://github.com/settings/tokens/new?scopes=repo&description=zenstruck%2Fchangelog');

        GitHubToken::save($token);

        $io->success('Saved personal access token.');

        return self::SUCCESS;
    }
}
