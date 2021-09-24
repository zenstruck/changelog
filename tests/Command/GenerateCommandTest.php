<?php

namespace Zenstruck\Changelog\Tests\Command;

use PHPUnit\Framework\TestCase;
use Zenstruck\Changelog\Command\GenerateCommand;
use Zenstruck\Console\Test\TestCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class GenerateCommandTest extends TestCase
{
    /**
     * @test
     */
    public function changelog_between_versions(): void
    {
        TestCommand::for(new GenerateCommand())
            ->addOption('repository', 'zenstruck/foundry')
            ->addOption('from', 'v1.13.0')
            ->addOption('to', 'v1.13.3')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Generating changelog for zenstruck/foundry:v1.13.0...v1.13.3')
            ->assertOutputContains('477db0a [minor] install psalm as composer-bin tool (#199) by @kbond')
            ->assertOutputContains('6ced887 [minor] add Symfony 5.4 to test matrix (#197) by @kbond')
            ->assertOutputContains('06b24d4 [bug] when creating collections, check for is persisting first (#195) by @jordisala1991, @kbond')
            ->assertOutputContains('5f39d8a [minor] update symfony-tools/docs-builder by @kbond')
            ->assertOutputContains('Done. View changeset on Github: https://github.com/zenstruck/foundry/compare/v1.13.0...v1.13.3')
        ;
    }

    /**
     * @test
     */
    public function changelog_with_no_previous_release(): void
    {
        TestCommand::for(new GenerateCommand())
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Generating changelog for zenstruck/.github:main')
            ->assertOutputContains('87cc344 Create FUNDING.yml by @kbond')
            ->assertOutputContains('Done. View changeset on Github: https://github.com/zenstruck/.github/commits/main')
        ;
    }

    /**
     * @test
     */
    public function can_guess_repository(): void
    {
        TestCommand::for(new GenerateCommand())
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Generating changelog for zenstruck/changelog:')
        ;
    }
}
