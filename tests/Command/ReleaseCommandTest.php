<?php

namespace Zenstruck\Changelog\Tests\Command;

use PHPUnit\Framework\TestCase;
use Zenstruck\Changelog\Command\ReleaseCommand;
use Zenstruck\Console\Test\TestCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ReleaseCommandTest extends TestCase
{
    /**
     * @test
     */
    public function next_major_with_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addOption('repository', 'kbond/changelog-test')
            ->addArgument('maj')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v2.0.0 (v1.1.0...v2.0.0)')
            ->assertOutputContains('Generating changelog for kbond/changelog-test:v1.1.0...main')
            ->assertOutputContains('f20c760 update by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/kbond/changelog-test/compare/v1.1.0...v2.0.0)')
            ->assertOutputContains('[NOTE] Preview only, pass --push option to create release on Github.')
            ->assertOutputNotContains('[changelog] add changelog')
        ;
    }

    /**
     * @test
     */
    public function next_major_interactive_with_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addOption('repository', 'kbond/changelog-test')
            ->addInput('major')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v2.0.0 (v1.1.0...v2.0.0)')
            ->assertOutputContains('Generating changelog for kbond/changelog-test:v1.1.0...main')
            ->assertOutputContains('f20c760 update by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/kbond/changelog-test/compare/v1.1.0...v2.0.0)')
            ->assertOutputContains('Not creating release.')
        ;
    }

    /**
     * @test
     */
    public function next_minor_with_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addOption('repository', 'kbond/changelog-test')
            ->addArgument('min')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v1.2.0 (v1.1.0...v1.2.0)')
            ->assertOutputContains('Generating changelog for kbond/changelog-test:v1.1.0...main')
            ->assertOutputContains('f20c760 update by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/kbond/changelog-test/compare/v1.1.0...v1.2.0)')
            ->assertOutputContains('[NOTE] Preview only, pass --push option to create release on Github.')
        ;
    }

    /**
     * @test
     */
    public function next_minor_interactive_with_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addOption('repository', 'kbond/changelog-test')
            ->addInput('feature')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v1.2.0 (v1.1.0...v1.2.0)')
            ->assertOutputContains('Generating changelog for kbond/changelog-test:v1.1.0...main')
            ->assertOutputContains('f20c760 update by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/kbond/changelog-test/compare/v1.1.0...v1.2.0)')
            ->assertOutputContains('Not creating release.')
        ;
    }

    /**
     * @test
     */
    public function next_patch_with_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addOption('repository', 'kbond/changelog-test')
            ->addArgument('bug')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v1.1.1 (v1.1.0...v1.1.1)')
            ->assertOutputContains('Generating changelog for kbond/changelog-test:v1.1.0...main')
            ->assertOutputContains('f20c760 update by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/kbond/changelog-test/compare/v1.1.0...v1.1.1)')
            ->assertOutputContains('[NOTE] Preview only, pass --push option to create release on Github.')
        ;
    }

    /**
     * @test
     */
    public function next_patch_interactive_with_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addOption('repository', 'kbond/changelog-test')
            ->addInput('bug')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v1.1.1 (v1.1.0...v1.1.1)')
            ->assertOutputContains('Generating changelog for kbond/changelog-test:v1.1.0...main')
            ->assertOutputContains('f20c760 update by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/kbond/changelog-test/compare/v1.1.0...v1.1.1)')
            ->assertOutputContains('Not creating release.')
        ;
    }

    /**
     * @test
     */
    public function next_major_with_no_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addArgument('major')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v1.0.0 (v1.0.0)')
            ->assertOutputContains('Generating changelog for zenstruck/.github:main')
            ->assertOutputContains('87cc344 Create FUNDING.yml by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/zenstruck/.github/commits/v1.0.0)')
        ;
    }

    /**
     * @test
     */
    public function next_major_interactive_with_no_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addInput('major')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v1.0.0 (v1.0.0)')
            ->assertOutputContains('Generating changelog for zenstruck/.github:main')
            ->assertOutputContains('87cc344 Create FUNDING.yml by @kbond')
            ->assertOutputContains('Not creating release.')
        ;
    }

    /**
     * @test
     */
    public function next_minor_with_no_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addArgument('feat')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v0.1.0 (v0.1.0)')
            ->assertOutputContains('Generating changelog for zenstruck/.github:main')
            ->assertOutputContains('87cc344 Create FUNDING.yml by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/zenstruck/.github/commits/v0.1.0)')
        ;
    }

    /**
     * @test
     */
    public function next_minor_interactive_with_no_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addInput('feature')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v0.1.0 (v0.1.0)')
            ->assertOutputContains('Generating changelog for zenstruck/.github:main')
            ->assertOutputContains('87cc344 Create FUNDING.yml by @kbond')
            ->assertOutputContains('Not creating release.')
        ;
    }

    /**
     * @test
     */
    public function next_patch_with_no_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addArgument('patch')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v0.0.1 (v0.0.1)')
            ->assertOutputContains('Generating changelog for zenstruck/.github:main')
            ->assertOutputContains('87cc344 Create FUNDING.yml by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/zenstruck/.github/commits/v0.0.1)')
        ;
    }

    /**
     * @test
     */
    public function next_patch_interactive_with_no_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addInput('bug')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v0.0.1 (v0.0.1)')
            ->assertOutputContains('Generating changelog for zenstruck/.github:main')
            ->assertOutputContains('87cc344 Create FUNDING.yml by @kbond')
            ->assertOutputContains('Not creating release.')
        ;
    }

    /**
     * @test
     */
    public function next_override_with_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addOption('repository', 'kbond/changelog-test')
            ->addArgument('v9.0.0')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v9.0.0 (v1.1.0...v9.0.0)')
            ->assertOutputContains('Generating changelog for kbond/changelog-test:v1.1.0...main')
            ->assertOutputContains('f20c760 update by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/kbond/changelog-test/compare/v1.1.0...v9.0.0)')
            ->assertOutputContains('[NOTE] Preview only, pass --push option to create release on Github.')
        ;
    }

    /**
     * @test
     */
    public function next_override_interactive_with_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addOption('repository', 'kbond/changelog-test')
            ->addInput('custom')
            ->addInput('v9.0.0')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v9.0.0 (v1.1.0...v9.0.0)')
            ->assertOutputContains('Generating changelog for kbond/changelog-test:v1.1.0...main')
            ->assertOutputContains('f20c760 update by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/kbond/changelog-test/compare/v1.1.0...v9.0.0)')
            ->assertOutputContains('Not creating release.')
        ;
    }

    /**
     * @test
     */
    public function next_override_with_no_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addArgument('v2.0.5')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v2.0.5 (v2.0.5)')
            ->assertOutputContains('Generating changelog for zenstruck/.github:main')
            ->assertOutputContains('87cc344 Create FUNDING.yml by @kbond')
            ->assertOutputContains('[Full Change List](https://github.com/zenstruck/.github/commits/v2.0.5)')
        ;
    }

    /**
     * @test
     */
    public function next_override_interactive_with_no_previous_release(): void
    {
        TestCommand::for(new ReleaseCommand())
            ->addInput('custom')
            ->addInput('v2.0.5')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
            ->assertSuccessful()
            ->assertOutputContains('Releasing as v2.0.5 (v2.0.5)')
            ->assertOutputContains('Generating changelog for zenstruck/.github:main')
            ->assertOutputContains('87cc344 Create FUNDING.yml by @kbond')
            ->assertOutputContains('Not creating release.')
        ;
    }

    /**
     * @test
     */
    public function next_invalid(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse semantic version type of "invalid".');

        TestCommand::for(new ReleaseCommand())
            ->addArgument('invalid')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
        ;
    }
}
