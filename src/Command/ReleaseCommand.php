<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Changelog\Factory;
use Zenstruck\Changelog\Version;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ReleaseCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('release')
            ->setDescription('Generate release')
            ->addArgument('next', InputArgument::REQUIRED, 'Release version, can use semantic type to auto-generate: major (maj), minor (min, feature, feat) or patch (bug, bugfix)')
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'Github repository use (leave blank to detect from current directory)')
            ->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'BASE to start release changelog from (leave blank for previous release)')
            ->addOption('target', 't', InputOption::VALUE_REQUIRED, 'Release target (leave blank for default branch)')
            ->addOption('push', null, InputOption::VALUE_NONE, 'Create the release on Github')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repository = (new Factory())->repository($input->getOption('repository'));
        $from = $input->getOption('from') ?? $repository->releases()->latest();
        $target = $input->getOption('target') ?? $repository->defaultBranch();
        $comparison = $repository->compare($target, $from);
        $next = new Version($input->getArgument('next'));
        $body = [];

        $io->title('Create release');

        if (!$next->isSemantic()) {
            $next = $from ? (new Version($from))->next($next) : Version::first($next);
        }

        $nextComparison = $repository->compare($next, $from);

        $io->comment("Releasing as <info>{$next}</info> (<comment>{$nextComparison}</comment>)");
        $io->comment("Generating changelog for <info>{$repository}:{$comparison}</info>");

        if ($comparison->isEmpty()) {
            throw new \RuntimeException('No commits.');
        }

        foreach ($comparison->commits() as $commit) {
            $io->writeln($body[] = $commit->format());
        }

        $io->writeln($body[] = '');
        $io->writeln($body[] = "[Full Change List]({$nextComparison->url()})");

        if (!$input->getOption('push')) {
            $io->note('Preview only, pass --push option to create release on Github.');

            return 0;
        }

        if ($input->isInteractive() && !$io->confirm("Create \"{$next}\" release on github?", false)) {
            $io->warning('Not creating release.');

            return 0;
        }

        $release = $repository->releases()->create($target, $next, \implode("\n", $body), $next->isPreRelease());

        $io->success("Released {$next}: {$release->url()}");

        return 0;
    }
}
