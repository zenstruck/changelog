<?php

/*
 * This file is part of the zenstruck/changelog package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\Path;
use Zenstruck\Changelog\ChangelogFile;
use Zenstruck\Changelog\Factory;
use Zenstruck\Changelog\Github\PendingRelease;
use Zenstruck\Changelog\Version;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FileUpdateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('file:update')
            ->setDescription('Update a changelog file for a pending release')
            ->addArgument('next', InputArgument::REQUIRED, 'Release version, can use semantic type to auto-generate: major (maj), minor (min, feature, feat) or patch (bug, bugfix)')
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'Github repository use (leave blank to detect from current directory)')
            ->addOption('filename', 'f', InputOption::VALUE_REQUIRED, 'The filename (relative to cwd)', 'CHANGELOG.md')
            ->addOption('target', 't', InputOption::VALUE_REQUIRED, 'Release target (leave blank for default branch)')
            ->addOption('remote', null, null, 'Save to repository')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filename = Path::canonicalize(\sprintf('%s/%s', \getcwd(), $input->getOption('filename')));
        $repository = (new Factory())->repository($input->getOption('repository'));
        $latest = $repository->releases()->latest();
        $target = $input->getOption('target');
        $remote = $input->getOption('remote');

        if (!$latest) {
            throw new \RuntimeException('No existing releases.');
        }

        $file = $remote ? ChangelogFile::fromRepositoryFile($repository, $input->getOption('filename'), $target) : ChangelogFile::fromLocalFile($repository, $filename);
        $release = new PendingRelease($repository, Version::nextFrom($input->getArgument('next'), $latest), $target);

        $io->title("Update CHANGELOG file for {$repository}");

        foreach ($file->update($release, $latest) as $line) {
            $io->writeln($line);
        }

        if ($remote) {
            $io->comment(\sprintf('Saving <comment>%s</comment> to <info>%s:%s</info>', $input->getOption('filename'), $repository, $target));

            $file->saveToRepositoryFile($input->getOption('filename'), $target);

            $io->success("Saved {$input->getOption('filename')} to {$repository}.");

            return self::SUCCESS;
        }

        $file->saveToLocalFile($filename);

        $io->success("Updated {$filename} with {$release}.");

        return self::SUCCESS;
    }
}
