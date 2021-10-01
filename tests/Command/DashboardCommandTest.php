<?php

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
