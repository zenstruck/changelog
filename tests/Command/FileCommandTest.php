<?php

namespace Zenstruck\Changelog\Tests\Command;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class FileCommandTest extends TestCase
{
    protected const FILE = __DIR__.'/../../var/CHANGELOG.md';

    protected function setUp(): void
    {
        (new Filesystem())->mkdir(\dirname(self::FILE));
        (new Filesystem())->remove(self::FILE);
    }
}
