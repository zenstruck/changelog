<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Changelog\Formatter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ReleaseCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('release')
            ->setDescription('Create a GitHub release')
            ->addArgument('next', InputArgument::REQUIRED, 'Next version, can use semantic type to auto-generate: major (maj), minor (min, feature, feat) or patch (bug, bugfix)')
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'GitHub repository use (leave blank to detect from current directory)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repository = $this->fetchRepository($input->getOption('repository'));
        $next = $repository->releases()->next($input->getArgument('next'));
        $comparison = $repository->compare();
        $formatter = new Formatter();
        $commits = $this->api()->commits($repository, $comparison);

        $io->title('Create Release');
        $io->comment("Creating <info>{$next}</info> release <info>{$repository}</info> (<comment>{$comparison}</comment>)");

        if ($commits->isEmpty()) {
            throw new \InvalidArgumentException("No commits for {$comparison}.");
        }

        $io->write($body = $formatter->releaseBody($commits));

        if ($input->isInteractive() && !$io->confirm('Are you sure you want to push this release to Github?', false)) {
            $io->warning('Not creating release.');

            return self::SUCCESS;
        }

        $release = $repository->createRelease($next, $body);

        $io->success("Done: {$release->url()}");

        return self::SUCCESS;
    }
}
