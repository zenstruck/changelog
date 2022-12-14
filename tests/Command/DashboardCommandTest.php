<?php

/*
 * This file is part of the zenstruck/changelog package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Changelog\Tests\Command;

use PHPUnit\Framework\TestCase;
use Zenstruck\Changelog\Command\DashboardCommand;
use Zenstruck\Console\Test\TestCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class DashboardCommandTest extends TestCase
{
    /**
     * @test
     */
    public function show_dashboard(): void
    {
        TestCommand::for(new DashboardCommand())
            ->splitOutputStreams()
            ->addArgument('zenstruck')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('zenstruck/foundry')
        ;
    }
}
