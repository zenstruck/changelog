<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Zenstruck\Changelog\ChangelogFile;
use Zenstruck\Changelog\Factory;
use Zenstruck\Changelog\Github\PendingRelease;
use Zenstruck\Changelog\Github\Release;
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
            ->addArgument('next', InputArgument::OPTIONAL, 'Release version, can use semantic type to auto-generate: major (maj), minor (min, feature, feat) or patch (bug, bugfix)')
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'Github repository use (leave blank to detect from current directory)')
            ->addOption('from', 'f', InputOption::VALUE_REQUIRED, 'BASE to start release changelog from (leave blank for previous release)')
            ->addOption('target', 't', InputOption::VALUE_REQUIRED, 'Release target (leave blank for default branch)')
            ->addOption('push', null, InputOption::VALUE_NONE, 'Create the release on Github')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'The changelog file', 'CHANGELOG.md')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repository = (new Factory())->repository($input->getOption('repository'));
        $from = $input->getOption('from') ?? $repository->releases()->latest();
        $target = $input->getOption('target') ?? $repository->defaultBranch();
        $comparison = $repository->compare($target, $from);
        $body = [];

        if ($comparison->isEmpty()) {
            throw new \RuntimeException('No commits.');
        }

        $io->title('Preview Changelog');

        $io->comment("Generating changelog for <info>{$repository}:{$comparison}</info>");

        foreach ($comparison->commits() as $commit) {
            $io->writeln($body[] = $commit->format());
        }

        $body = \implode("\n", $body);

        $io->title('Create Release');

        if ($input->isInteractive() && !$input->getArgument('next')) {
            if ($from) {
                $io->text("Latest release for <comment>{$repository}</comment>: <info>{$from}</info>");
            } else {
                $io->text("No releases for <comment>{$repository}</comment> yet");
            }

            $version = new Version($from ?: 'v0.0.0');
            $next = $io->choice(
                \sprintf("What's the %s version?", $from ? 'next' : 'first'),
                [
                    'bug' => (string) $version->next('bug'),
                    'feature' => (string) $version->next('feature'),
                    'major' => (string) $version->next('major'),
                    'custom' => 'Enter version manually',
                ],
                str_contains($body, 'feature') ? 'feature' : null
            );

            if ('custom' === $next) {
                $next = $io->ask('Next version');
            }

            $input->setArgument('next', $next);
        }

        if (!$next = $input->getArgument('next')) {
            throw new \RuntimeException('The "next" argument is required.');
        }

        $release = new PendingRelease($repository, Version::nextFrom($next, $from), $target);
        $nextComparison = $release->compareFrom($from);

        $io->comment("Releasing as <info>{$release}</info> (<comment>{$nextComparison}</comment>)");

        $body .= "\n\n[Full Change List]({$nextComparison->url()})";
        $io->writeln($body);

        if (!$input->isInteractive() && !$input->getOption('push')) {
            $io->note('Preview only, pass --push option to create release on Github.');

            return self::SUCCESS;
        }

        if ($input->isInteractive() && !$io->confirm("Create \"{$release}\" release on github?", false)) {
            $io->warning('Not creating release.');

            return self::SUCCESS;
        }

        try {
            $file = $repository->getFile($input->getOption('file'), $target);
        } catch (ClientExceptionInterface $e) {
            $file = null;
        }

        if ($input->isInteractive() && $file && $from instanceof Release && $io->confirm("Update \"{$file}\"?")) {
            $changelogFile = ChangelogFile::fromRepositoryFile($repository, $file, $target);

            foreach ($changelogFile->update($release, $from) as $line) {
                $io->writeln($line, OutputInterface::VERBOSITY_VERBOSE);
            }

            $io->comment(\sprintf('Saving <comment>%s</comment> to <info>%s:%s</info>', $file, $repository, $target));

            $changelogFile->saveToRepositoryFile($file, $target);
        }

        $release = $release->setBody($body)->create();

        $io->success("Released {$release}: {$release->url()}");

        return self::SUCCESS;
    }
}
