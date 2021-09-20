<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class PreviewCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('preview')
            ->setDescription('Preview changelog')
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'GitHub repository use (leave blank to detect from current directory)')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'Release to start changelog from (leave blank for latest)')
            ->addOption('to', null, InputOption::VALUE_REQUIRED, 'Release to end changelog (leave blank for default branch)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Changelog Preview');

        $io->success('Done.');

        return self::SUCCESS;
    }
}
