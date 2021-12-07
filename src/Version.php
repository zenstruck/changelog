<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Version
{
    public const MAJOR = 'major';
    public const MINOR = 'minor';
    public const PATCH = 'patch';

    public const STABLE = 'stable';
    public const RC = 'rc';
    public const BETA = 'beta';
    public const ALPHA = 'alpha';

    private string $value;
    private array $parts;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public static function nextFrom(string $next, ?string $from = null)
    {
        $version = new self($next);

        if (!$version->isSemantic()) {
            $version = $from ? (new self($from))->next($next) : self::first($next);
        }

        return $version;
    }

    public static function first(string $type): self
    {
        return (new self('v0.0.0'))->next($type);
    }

    public function next(string $type): self
    {
        if (!$this->isStable()) {
            throw new \LogicException("Cannot calculate next version for unstable versions ({$this}).");
        }

        switch (self::normalizeType($type)) {
            case self::MAJOR:
                return new self(\sprintf('v%d.0.0', $this->major() + 1));
            case self::MINOR:
                return new self(\sprintf('v%d.%d.0', $this->major(), $this->minor() + 1));
        }

        return new self(\sprintf('v%d.%d.%d', $this->major(), $this->minor(), $this->patch() + 1));
    }

    public function isSemantic(): bool
    {
        try {
            $this->parts();

            return true;
        } catch (\InvalidArgumentException $e) {
            return false;
        }
    }

    public function major(): int
    {
        return $this->parts()['major'];
    }

    public function minor(): int
    {
        return $this->parts()['minor'];
    }

    public function patch(): int
    {
        return $this->parts()['patch'];
    }

    public function stability(): string
    {
        return $this->parts()['stability'];
    }

    public function isStable(): bool
    {
        return self::STABLE === $this->stability();
    }

    public function isPreRelease(): bool
    {
        if (!$this->isSemantic()) {
            return false;
        }

        if (0 === $this->major()) {
            return true;
        }

        if (\in_array($this->stability(), [self::ALPHA, self::BETA, self::RC], true)) {
            return true;
        }

        return false;
    }

    /**
     * @return array{major: int, minor: int, patch: int, stability: string}
     */
    private function parts(): array
    {
        if (isset($this->parts)) {
            return $this->parts;
        }

        if (!\preg_match('#^[vV]?(\d+)\.(\d+)\.(\d+)(.+)?$#', $this->value, $matches)) {
            throw new \InvalidArgumentException("\"{$this->value}\" is not a valid semantic version number.");
        }

        if (!$stability = self::parseStability($matches[4] ?? null)) {
            throw new \InvalidArgumentException("Unable to parse stability of \"{$this->value}\".");
        }

        return $this->parts = [
            'major' => (int) $matches[1],
            'minor' => (int) $matches[2],
            'patch' => (int) $matches[3],
            'stability' => $stability,
        ];
    }

    private static function parseStability(?string $suffix): ?string
    {
        if (!$suffix = \mb_strtolower($suffix)) {
            return self::STABLE;
        }

        switch (true) {
            case str_contains($suffix, self::RC):
                return self::RC;

            case str_contains($suffix, self::BETA):
                return self::BETA;

            case str_contains($suffix, self::ALPHA):
                return self::ALPHA;
        }

        return null;
    }

    private static function normalizeType(string $type): string
    {
        switch (\mb_strtolower($type)) {
            case 'major':
            case 'maj':
                return self::MAJOR;
            case 'min':
            case 'minor':
            case 'feature':
            case 'feat':
                return self::MINOR;
            case 'patch':
            case 'bug':
            case 'bugfix':
                return self::PATCH;
        }

        throw new \InvalidArgumentException("Unable to parse semantic version type of \"{$type}\".");
    }
}
