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

    private string $raw;
    private ?string $prefix;
    private array $parts;

    public function __construct(string $value)
    {
        // todo alpha/beta/rc, better prefix support?
        $this->raw = $value;
        $this->prefix = str_starts_with($value, 'v') ? 'v' : null;
        $this->parts = \explode('.', \ltrim($value, $this->prefix));
    }

    public function __toString(): string
    {
        return $this->raw;
    }

    public static function first(string $value): self
    {
        // todo make prefix configurable
        return (new self('v0.0.0'))->next($value);
    }

    public function compareWith(?string $from = null): Comparison
    {
        return new Comparison($this, $from);
    }

    public function next(string $value): self
    {
        if (!$type = self::normalizeType($value)) {
            return new self($value);
        }

        if (3 !== \count($this->parts)) {
            throw new \RuntimeException('Unable to parse semantic version.');
        }

        foreach ($this->parts as $part) {
            if (!\is_numeric($part)) {
                throw new \RuntimeException('Unable to parse semantic version.');
            }
        }

        switch ($type) {
            case self::MAJOR:
                return new self(\sprintf('%s%d.0.0', $this->prefix, $this->parts[0] + 1));
            case self::MINOR:
                return new self(\sprintf('%s%d.%d.0', $this->prefix, $this->parts[0], $this->parts[1] + 1));
            case self::PATCH:
                return new self(\sprintf('%s%d.%d.%d', $this->prefix, $this->parts[0], $this->parts[1], $this->parts[2] + 1));
        }

        throw new \InvalidArgumentException('Unknown semantic version type.');
    }

    private static function normalizeType(string $type): ?string
    {
        switch ($type) {
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

        return null;
    }
}
