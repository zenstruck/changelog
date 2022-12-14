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

use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class SelfUpdateCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->setName('self-update')
            ->setAliases(['selfupdate'])
            ->setDescription('Updates zenstruck/changelog to the latest version')
            ->addOption('rollback', 'r', InputOption::VALUE_NONE, 'Rollback to a previous version')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $updater = new Updater(null, false, Updater::STRATEGY_GITHUB);
        $updater->setBackupPath($backup = \sys_get_temp_dir().'/changelog.phar.bak');
        $updater->setRestorePath($backup);

        if ($input->getOption('rollback')) {
            if ($updater->rollback()) {
                $io->success('Successfully rolled back.');

                return self::SUCCESS;
            }

            throw new \RuntimeException('Could not rollback.');
        }

        $updater->getStrategy()->setPackageName('zenstruck/changelog');
        $updater->getStrategy()->setPharName('changelog.phar');
        $updater->getStrategy()->setCurrentLocalVersion($current = $this->getApplication()->getVersion());

        if (!$updater->update()) {
            $io->success(\sprintf('You are already using the latest available zenstruck/changelog (%s).', $current));

            return self::SUCCESS;
        }

        // cannot use $io->success() it uses symfony/string and older versions may not have it included.
        // see https://github.com/zenstruck/changelog/pull/2
        $io->writeln("<info>Successfully updated to {$updater->getNewVersion()}.</info>");

        return self::SUCCESS;
    }
}
