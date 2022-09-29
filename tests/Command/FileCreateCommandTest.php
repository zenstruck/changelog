<?php

namespace Zenstruck\Changelog\Tests\Command;

use Symfony\Component\Filesystem\Filesystem;
use Zenstruck\Changelog\Command\FileCreateCommand;
use Zenstruck\Console\Test\TestCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FileCreateCommandTest extends FileCommandTest
{
    /**
     * @test
     */
    public function can_create(): void
    {
        $expectedOutput = [
            '# CHANGELOG',
            '## [v1.1.0](https://github.com/kbond/changelog-test/releases/tag/v1.1.0)',
            'September 20th, 2021 - [v1.0.0...v1.1.0](https://github.com/kbond/changelog-test/compare/v1.0.0...v1.1.0)',
            '* 656e546 Update README.md by @kbond',
            '## [v1.0.0](https://github.com/kbond/changelog-test/releases/tag/v1.0.0)',
            'September 20th, 2021 - _[Initial Release](https://github.com/kbond/changelog-test/commits/v1.0.0)_',
        ];
        $unexpectedOutput = ['* 917c756 Initial commit by @kbond'];

        $this->assertFileDoesNotExist(self::FILE);

        TestCommand::for(new FileCreateCommand())
            ->addOption('filename', 'var/CHANGELOG.md')
            ->addOption('repository', 'kbond/changelog-test')
            ->addOption('verbose')
            ->execute()
            ->assertSuccessful()
        ;

        $this->assertFileExists(self::FILE);

        $fileContents = \file_get_contents(self::FILE);

        foreach ($expectedOutput as $expected) {
            $this->assertStringContainsString($expected, $fileContents);
        }

        foreach ($unexpectedOutput as $expected) {
            $this->assertStringNotContainsString($expected, $fileContents);
        }
    }

    /**
     * @test
     */
    public function aborts_if_file_already_exists(): void
    {
        (new Filesystem())->touch(self::FILE);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Aborting as the changelog file already exists.');

        TestCommand::for(new FileCreateCommand())
            ->addOption('filename', 'var/CHANGELOG.md')
            ->addOption('repository', 'kbond/changelog-test')
            ->execute()
        ;
    }

    /**
     * @test
     */
    public function can_override_if_file_exists(): void
    {
        (new Filesystem())->touch(self::FILE);

        TestCommand::for(new FileCreateCommand())
            ->addOption('filename', 'var/CHANGELOG.md')
            ->addOption('repository', 'kbond/changelog-test')
            ->addInput('y')
            ->execute()
            ->assertSuccessful()
        ;
    }

    /**
     * @test
     */
    public function aborts_if_no_releases(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No releases available for zenstruck/.github.');

        TestCommand::for(new FileCreateCommand())
            ->addOption('filename', 'var/CHANGELOG.md')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
        ;
    }
}
