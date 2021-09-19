<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
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
            ->addOption('repository', 'r', InputOption::VALUE_REQUIRED, 'GitHub repository use (leave blank to detect from current directory)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $repository = $this->fetchRepository($input->getOption('repository'));
        $formatter = new Formatter();

        $io->title('Changelog Preview');
        $io->comment("Generating changelog for <info>{$repository}</info>");

        $io->write($formatter->changelogHeader());

        foreach ($repository->releases() as $release) {
            $io->write($formatter->release(
                $release,
                $this->api()->commits($repository, $release->compareWithPrevious())
            ));
        }

        $io->success('Done.');

        return self::SUCCESS;
    }
}
