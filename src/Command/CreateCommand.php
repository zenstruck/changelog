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
final class CreateCommand extends BaseCommand
{
    protected function configure(): void
    {
        $this
            ->setName('create')
            ->setDescription('Create changelog based on GitHub releases')
            ->addArgument('file', InputArgument::OPTIONAL, 'The changelog filename (ie CHANGELOG.md)')
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'GitHub repository use (leave blank to detect from current directory)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repository = $this->fetchRepository($input->getOption('repository'));
        $formatter = new Formatter();
        $contents = '';
        $releases = $repository->releases();

        if ($releases->isEmpty()) {
            throw new \RuntimeException("No releases for {$repository}");
        }

        $io->title('Changelog Preview');
        $io->comment("Generating changelog for <info>{$repository}</info>");

        $io->write($contents .= $formatter->changelogHeader());

        foreach ($releases as $release) {
            $io->write($value = $formatter->release(
                $release,
                $this->api()->commits($repository, $release->compareWithPrevious())
            ));

            $contents .= $value;
        }

        if ($file = $input->getArgument('file')) {
            (new Filesystem())->dumpFile(getcwd().'/'.$file, $contents);

            $io->success("Done. Saved as '{$file}'");

            return self::SUCCESS;
        }

        $io->success('Done.');

        return self::SUCCESS;
    }
}
