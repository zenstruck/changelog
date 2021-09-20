<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Zenstruck\Changelog\Formatter;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class UpdateCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('update')
            ->setDescription('Update changelog file for next release')
            ->addArgument('next', InputArgument::REQUIRED, 'Next version, can use semantic type to auto-generate: major (maj), minor (min, feature, feat) or patch (bug, bugfix)')
            ->addArgument('file', InputArgument::OPTIONAL, 'The changelog filename', 'CHANGELOG.md')
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'GitHub repository use (leave blank to detect from current directory)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repository = $this->fetchRepository($input->getOption('repository'));
        $next = $repository->releases()->next($input->getArgument('next'));
        $file = $input->getArgument('file');
        $comparison = $repository->compare();
        $commits = $this->api()->commits($repository, $comparison);
        $formatter = new Formatter();

        if (!\file_exists($file)) {
            throw new \InvalidArgumentException('Changelog file does not exist, run "changelog create".');
        }

        if ($commits->isEmpty()) {
            throw new \InvalidArgumentException("No commits for {$comparison}.");
        }

        $io->title('Update Changelog');
        $io->comment("Updating changelog for <info>{$repository}</info>");

        $io->write($value = $formatter->release($next, $commits));

        $contents = \file_get_contents($file);

        if (str_contains($contents, $value)) {
            throw new \RuntimeException('Changelog already contains this release.');
        }

        $contents = \str_replace(
            $formatter->changelogHeader(),
            $formatter->changelogHeader().$value,
            \file_get_contents($file)
        );

        (new Filesystem())->dumpFile($file, $contents);

        $io->success("Done. Updated {$file}");

        return self::SUCCESS;
    }
}
