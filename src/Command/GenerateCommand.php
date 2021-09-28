<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Changelog\Factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class GenerateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('generate')
            ->setDescription('Generate changelog')
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'Github repository use (leave blank to detect from current directory)')
            ->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'BASE to start changelog from (leave blank for previous release)')
            ->addOption('to', 't', InputOption::VALUE_REQUIRED, 'HEAD to end changelog (leave blank for default branch)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repository = (new Factory())->repository($input->getOption('repository'));
        $comparison = $repository->compare(
            $input->getOption('to') ?? $repository->defaultBranch(),
            $input->getOption('from') ?? $repository->releases()->latest()
        );

        $io->title('Generate Changelog');
        $io->comment("Generating changelog for <info>{$repository}:{$comparison}</info>");

        if ($comparison->isEmpty()) {
            $io->warning('No commits.');

            return self::SUCCESS;
        }

        foreach ($comparison->commits() as $commit) {
            $io->writeln($commit->format());
        }

        $io->success("Done. View changeset on Github: {$comparison->url()}");

        return self::SUCCESS;
    }
}
