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
