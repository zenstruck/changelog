<?php

namespace Zenstruck\Changelog\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Changelog\Configuration;
use Zenstruck\Changelog\Factory;
use Zenstruck\Changelog\Github\Repository;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DashboardCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('dashboard')
            ->setDescription('Show the release status packages in organization(s)')
            ->addArgument('organization', InputArgument::OPTIONAL, 'The Github organization')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $factory = new Factory();
        $organization = $input->getArgument('organization');
        $default = $factory->configuration()->get(Configuration::DEFAULT_DASHBOARD_ORG);

        $io->title('Release Status Dashboard');

        if (!$organization && !$default) {
            if (!$input->isInteractive()) {
                throw new \RuntimeException('The organization argument is required.');
            }

            $default = $io->ask('Github organization');

            if ($io->confirm('Save as default?', false)) {
                $factory->configuration()->set(Configuration::DEFAULT_DASHBOARD_ORG, $default);
                $io->comment("{$default} saved as default.");
            }
        }

        if (!$organization && $default) {
            $organization = $default;
        }

        $table = new Table($output->section());
        $table->setHeaderTitle($organization);
        $table->setHeaders(['Repository', 'Status']);

        $table->render();

        foreach ($factory->repositoriesFor($organization) as $repository) {
            if (str_starts_with($repository->name(), '.')) {
                // exclude repositories that begin with "."
                continue;
            }

            $table->appendRow([(string) $repository, self::releaseStatus($repository)]);
        }

        return self::SUCCESS;
    }

    private static function releaseStatus(Repository $repository): string
    {
        if (!$latest = $repository->releases()->latest()) {
            return '<error>no releases</error>';
        }

        $unreleased = $repository
            ->compare($repository->defaultBranch(), $latest)
            ->commits()
            ->withoutMerges()
            ->withoutChangelogUpdates()
            ->count()
        ;

        return $unreleased ? "<comment>unreleased commits ({$unreleased})</comment>" : '<info>up to date</info>';
    }
}
