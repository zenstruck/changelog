<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Changelog\Factory;
use Zenstruck\Changelog\Github\PendingRelease;
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
        $release = new PendingRelease(
            $repository,
            Version::nextFrom($input->getArgument('next'), $from),
            $input->getOption('target')
        );
        $comparison = $release->realComparison($from);
        $body = [];

        $io->title('Create release');

        $nextComparison = $release->compareFrom($from);

        $io->comment("Releasing as <info>{$release}</info> (<comment>{$nextComparison}</comment>)");
        $io->comment("Generating changelog for <info>{$repository}:{$comparison}</info>");

        if ($comparison->isEmpty()) {
            throw new \RuntimeException('No commits.');
        }

        foreach ($comparison->commits() as $commit) {
            $io->writeln($body[] = $commit->format());
        }

        $io->writeln($body[] = '');
        $io->writeln($body[] = "[Full Change List]({$nextComparison->url()})");

        if (!$input->isInteractive() && !$input->getOption('push')) {
            $io->note('Preview only, pass --push option to create release on Github.');

            return self::SUCCESS;
        }

        if ($input->isInteractive() && !$io->confirm("Create \"{$release}\" release on github?", false)) {
            $io->warning('Not creating release.');

            return 0;
        }

        $release->setBody(\implode("\n", $body));

        $release = $release->create();

        $io->success("Released {$release}: {$release->url()}");

        return self::SUCCESS;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        if ($input->getArgument('next')) {
            return;
        }

        $repository = (new Factory())->repository($input->getOption('repository'));
        $latest = (string) $repository->releases()->latest();
        $io = new SymfonyStyle($input, $output);

        if ($latest) {
            $io->text("Latest release for <comment>{$repository}</comment>: <info>{$latest}</info>");
        } else {
            $io->text('No releases for <comment>{$repository}</comment> yet');
        }

        $version = new Version($latest ?: 'v0.0.0');

        $next = $io->choice(\sprintf("What's the %s version?", $latest ? 'next' : 'first'), [
            'bug' => (string) $version->next('bug'),
            'feature' => (string) $version->next('feature'),
            'major' => (string) $version->next('major'),
            'custom' => 'Enter version manually',
        ]);

        if ('custom' === $next) {
            $next = $io->ask('Next version');
        }

        $input->setArgument('next', $next);
    }
}
