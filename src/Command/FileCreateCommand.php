<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Zenstruck\Changelog\ChangelogFile;
use Zenstruck\Changelog\Factory;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FileCreateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('file:create')
            ->setDescription('Create a changelog file for existing releases')
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'Github repository use (leave blank to detect from current directory)')
            ->addOption('filename', 'f', InputOption::VALUE_REQUIRED, 'The filename (relative to cwd)', 'CHANGELOG.md')
            ->addOption('exclude-pre-releases', null, InputOption::VALUE_NONE, 'Exclude "pre-releases"')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $fs = new Filesystem();
        $filename = Path::canonicalize(\sprintf('%s/%s', \getcwd(), $input->getOption('filename')));
        $repository = (new Factory())->repository($input->getOption('repository'));

        $io->title("Create CHANGELOG file for {$repository}");

        if ($fs->exists($filename) && (!$input->isInteractive() || !$io->confirm('Changelog file already exists, override?', false))) {
            throw new \RuntimeException('Aborting as the changelog file already exists.');
        }

        $releases = $repository->releases();

        if ($input->getOption('exclude-pre-releases')) {
            $releases = $releases->withoutPreReleases();
        }

        $file = new ChangelogFile($repository, $filename);

        foreach ($file->create($releases) as $line) {
            $io->writeln($line, OutputInterface::VERBOSITY_VERBOSE);
        }

        $io->success("Created {$filename}.");

        return self::SUCCESS;
    }
}
