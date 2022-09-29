<?php

namespace Zenstruck\Changelog\Tests;

use PHPUnit\Framework\TestCase;
use Zenstruck\Changelog\Version;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class VersionTest extends TestCase
{
    /**
     * @test
     */
    public function is_stringable(): void
    {
        $this->assertSame('v1.2.3', (string) new Version('v1.2.3'));
        $this->assertSame('v1.0.0-BETA1', (string) new Version('v1.0.0-BETA1'));
        $this->assertSame('foo', (string) new Version('foo'));
    }

    /**
     * @test
     */
    public function next_from(): void
    {
        $this->assertSame('v1.0.0', (string) Version::nextFrom('major'));
        $this->assertSame('v1.2.0', (string) Version::nextFrom('min', 'v1.1.0'));
        $this->assertSame('v1.1.0', (string) Version::nextFrom('v1.1.0'));
    }

    /**
     * @test
     */
    public function can_parse_parts(): void
    {
        $version = new Version('v1.2.3');

        $this->assertTrue($version->isSemantic());
        $this->assertSame(1, $version->major());
        $this->assertSame(2, $version->minor());
        $this->assertSame(3, $version->patch());

        $version = new Version('1.2.3');

        $this->assertTrue($version->isSemantic());
        $this->assertSame(1, $version->major());
        $this->assertSame(2, $version->minor());
        $this->assertSame(3, $version->patch());

        $version = new Version('v1.2.3-BETA1');

        $this->assertTrue($version->isSemantic());
        $this->assertSame(1, $version->major());
        $this->assertSame(2, $version->minor());
        $this->assertSame(3, $version->patch());

        $version = new Version('v123.456.789');

        $this->assertTrue($version->isSemantic());
        $this->assertSame(123, $version->major());
        $this->assertSame(456, $version->minor());
        $this->assertSame(789, $version->patch());
    }

    /**
     * @test
     *
     * @dataProvider invalidVersions
     */
    public function version_parse_failure(Version $version): void
    {
        $this->assertFalse($version->isSemantic());

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("\"{$version}\" is not a valid semantic version number.");

        $version->major();
    }

    public static function invalidVersions(): iterable
    {
        yield [new Version('1.2')];
        yield [new Version('foo')];
        yield [new Version('1')];
    }

    /**
     * @test
     */
    public function can_parse_stability(): void
    {
        $this->assertSame(Version::STABLE, (new Version('v1.2.3'))->stability());
        $this->assertSame(Version::STABLE, (new Version('1.2.3'))->stability());

        $this->assertSame(Version::RC, (new Version('1.2.3-RC'))->stability());
        $this->assertSame(Version::RC, (new Version('1.2.3-RC1'))->stability());
        $this->assertSame(Version::RC, (new Version('1.2.3RC'))->stability());
        $this->assertSame(Version::RC, (new Version('1.2.3_rc'))->stability());

        $this->assertSame(Version::BETA, (new Version('1.2.3-BETA'))->stability());
        $this->assertSame(Version::BETA, (new Version('1.2.3-BETA1'))->stability());
        $this->assertSame(Version::BETA, (new Version('1.2.3BETA'))->stability());
        $this->assertSame(Version::BETA, (new Version('1.2.3_beta'))->stability());

        $this->assertSame(Version::ALPHA, (new Version('1.2.3-ALPHA'))->stability());
        $this->assertSame(Version::ALPHA, (new Version('1.2.3-ALPHA1'))->stability());
        $this->assertSame(Version::ALPHA, (new Version('1.2.3ALPHA'))->stability());
        $this->assertSame(Version::ALPHA, (new Version('1.2.3_alpha'))->stability());
    }

    /**
     * @test
     *
     * @dataProvider invalidStabilities
     */
    public function stability_parse_failure(Version $version): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Unable to parse stability of \"{$version}\".");

        $version->stability();
    }

    public static function invalidStabilities(): iterable
    {
        yield [new Version('v1.2.3-foo')];
        yield [new Version('v1.2.3-')];
    }

    /**
     * @test
     */
    public function can_create_next_version(): void
    {
        $version = new Version('v1.2.3');

        $this->assertSame('v2.0.0', (string) $version->next('maj'));
        $this->assertSame('v2.0.0', (string) $version->next('MAJ'));
        $this->assertSame('v2.0.0', (string) $version->next('major'));

        $this->assertSame('v1.3.0', (string) $version->next('min'));
        $this->assertSame('v1.3.0', (string) $version->next('minor'));
        $this->assertSame('v1.3.0', (string) $version->next('feat'));
        $this->assertSame('v1.3.0', (string) $version->next('feature'));

        $this->assertSame('v1.2.4', (string) $version->next('patch'));
        $this->assertSame('v1.2.4', (string) $version->next('bug'));
        $this->assertSame('v1.2.4', (string) $version->next('bugfix'));
    }

    /**
     * @test
     */
    public function next_version_always_adds_prefix(): void
    {
        $this->assertSame('v2.0.0', (string) (new Version('1.2.3'))->next('maj'));
    }

    /**
     * @test
     */
    public function cannot_create_next_version_for_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse semantic version type of "invalid".');

        (new Version('v1.2.3'))->next('invalid');
    }

    /**
     * @test
     */
    public function cannot_create_next_version_for_non_semantic_versions(): void
    {
        $version = new Version('invalid');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("\"{$version}\" is not a valid semantic version number.");

        $version->next('maj');
    }

    /**
     * @test
     */
    public function cannot_create_next_version_from_unstable_version(): void
    {
        $version = new Version('v1.0.0-BETA1');

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage("Cannot calculate next version for unstable versions ({$version}).");

        $version->next('min');
    }

    /**
     * @test
     */
    public function can_create_first_version(): void
    {
        $this->assertSame('v1.0.0', (string) Version::first('maj'));
        $this->assertSame('v0.1.0', (string) Version::first('min'));
        $this->assertSame('v0.0.1', (string) Version::first('bug'));
    }

    /**
     * @test
     */
    public function cannot_create_first_version_for_invalid_type(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unable to parse semantic version type of "invalid".');

        Version::first('invalid');
    }

    /**
     * @test
     */
    public function can_check_if_pre_release(): void
    {
        $this->assertFalse((new Version('invalid'))->isPreRelease());
        $this->assertFalse((new Version('v1.0.0'))->isPreRelease());
        $this->assertFalse((new Version('1.0.0'))->isPreRelease());
        $this->assertTrue((new Version('v1.0.0-BETA'))->isPreRelease());
        $this->assertTrue((new Version('v1.0.0-ALPHA1'))->isPreRelease());
        $this->assertTrue((new Version('v1.0.0-RC2'))->isPreRelease());
        $this->assertTrue((new Version('v0.2.0'))->isPreRelease());
        $this->assertTrue((new Version('0.0.1'))->isPreRelease());
    }
}
