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
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableCellStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Zenstruck\Changelog\Configuration;
use Zenstruck\Changelog\Factory;
use Zenstruck\Changelog\Github\Release;
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
            ->addOption('enable-workflows', null, InputOption::VALUE_NONE, 'Enable workflows disabled due to inactivity')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $factory = new Factory();
        $organization = $input->getArgument('organization');
        $default = $factory->configuration()->get(Configuration::DEFAULT_DASHBOARD_ORG);
        $enableWorkflows = $input->getOption('enable-workflows');

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
        $table->setHeaders([
            'Repository',
            'Latest Release',
            'Status',
            'Issues',
            'PRs',
            new TableCell('<info>CI?</info>', [
                'style' => new TableCellStyle(['align' => 'center']),
            ]),
        ]);

        $table->render();

        foreach ($factory->repositoriesFor($organization) as $repository) {
            if (0 === $repository->releases()->count()) {
                // exclude repositories with no releases
                continue;
            }

            if ($repository->isArchived()) {
                continue;
            }

            $table->appendRow([
                (string) $repository,
                self::formatLatest($repository->releases()->latest()),
                self::releaseStatus($repository),
                new TableCell($repository->openIssues(), [
                    'style' => new TableCellStyle(['align' => 'center']),
                ]),
                new TableCell($repository->openPullRequests(), [
                    'style' => new TableCellStyle(['align' => 'center']),
                ]),
                new TableCell(self::formatCI($repository), [
                    'style' => new TableCellStyle(['align' => 'center']),
                ]),
            ]);

            if ($enableWorkflows) {
                foreach ($repository->workflows() as $workflow) {
                    if ('disabled_inactivity' === $workflow['state']) {
                        $repository->enableWorkflow($workflow['id']);
                    }
                }
            }
        }

        return self::SUCCESS;
    }

    private static function formatCI(Repository $repository): string
    {
        if ('active' !== ($repository->workflows()[0]['state'] ?? null)) {
            return '<comment>(disabled)</comment>';
        }

        if (!$latestRun = $repository->workflowRuns()[0] ?? []) {
            return '<comment>(none)</comment>';
        }

        return 'success' === ($latestRun['conclusion'] ?? null) ? '<info>✔</info>' : '<fg=red>✖</>';
    }

    private static function formatLatest(?Release $release): string
    {
        if (!$release) {
            return '<error>none</>';
        }

        if ($release->isPreRelease()) {
            return "<comment>{$release->tagName()}</comment>";
        }

        return "<info>{$release->tagName()}</info>";
    }

    private static function releaseStatus(Repository $repository): string
    {
        if (!$latest = $repository->releases()->latest()) {
            return '<error>no releases</error>';
        }

        $unreleased = $repository
            ->compare($repository->defaultBranch(), $latest->tagName())
            ->commits()
            ->count()
        ;

        return $unreleased ? "<comment>unreleased commits ({$unreleased})</comment>" : '<info>up to date</info>';
    }
}
