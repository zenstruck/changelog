<?php

namespace Zenstruck\Changelog\Tests\Command;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Path;
use Zenstruck\Changelog\Command\FileUpdateCommand;
use Zenstruck\Console\Test\TestCommand;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class FileUpdateCommandTest extends FileCommandTest
{
    /**
     * @test
     */
    public function can_update(): void
    {
        $previous = '## [v1.1.0](https://github.com/kbond/changelog-test/releases/tag/v1.1.0)';

        (new Filesystem())->dumpFile(self::FILE, "# CHANGELOG\n\n{$previous}");

        $expectedOutput = [
            '## [v1.2.0](https://github.com/kbond/changelog-test/releases/tag/v1.2.0)',
            \sprintf('%s - [v1.1.0...v1.2.0](https://github.com/kbond/changelog-test/compare/v1.1.0...v1.2.0)', (new \DateTime())->format('F jS, Y')),
            '* f20c760 update by @kbond',
        ];

        $result = TestCommand::for(new FileUpdateCommand())
            ->addArgument('feat')
            ->addOption('filename', 'var/CHANGELOG.md')
            ->addOption('repository', 'kbond/changelog-test')
            ->execute()
            ->assertSuccessful()
        ;

        $fileContents = \file_get_contents(self::FILE);

        foreach ($expectedOutput as $expected) {
            $result->assertOutputContains($expected);
            $this->assertStringContainsString($expected, $fileContents);
        }

        $this->assertStringContainsString($previous, $fileContents);
    }

    /**
     * @test
     */
    public function cannot_update_if_no_file(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(\sprintf('Changelog file "%s" does not exist.', Path::canonicalize(self::FILE)));

        TestCommand::for(new FileUpdateCommand())
            ->addArgument('feat')
            ->addOption('filename', 'var/CHANGELOG.md')
            ->addOption('repository', 'kbond/changelog-test')
            ->execute()
        ;
    }

    /**
     * @test
     */
    public function cannot_update_if_file_in_invalid_format(): void
    {
        (new Filesystem())->touch(self::FILE);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Changelog is not in the proper format.');

        TestCommand::for(new FileUpdateCommand())
            ->addArgument('feat')
            ->addOption('filename', 'var/CHANGELOG.md')
            ->addOption('repository', 'kbond/changelog-test')
            ->execute()
        ;
    }

    /**
     * @test
     */
    public function cannot_update_if_release_already_in_file(): void
    {
        (new Filesystem())->dumpFile(self::FILE, "# CHANGELOG\n\n## [v1.2.0](https://github.com/kbond/changelog-test/releases/tag/v1.2.0)");

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Changelog already contains changes for v1.2.0.');

        TestCommand::for(new FileUpdateCommand())
            ->addArgument('feat')
            ->addOption('filename', 'var/CHANGELOG.md')
            ->addOption('repository', 'kbond/changelog-test')
            ->execute()
        ;
    }

    /**
     * @test
     */
    public function cannot_update_if_no_previous_release(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('No existing releases.');

        TestCommand::for(new FileUpdateCommand())
            ->addArgument('feat')
            ->addOption('filename', 'var/CHANGELOG.md')
            ->addOption('repository', 'zenstruck/.github')
            ->execute()
        ;
    }
}
