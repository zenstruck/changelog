<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Repository
{
    private array $raw;
    private GitHubApi $api;

    public function __construct(array $raw, GitHubApi $api)
    {
        $this->raw = $raw;
        $this->api = $api;
    }

    public function __toString(): string
    {
        return $this->raw['full_name'];
    }

    public function compare(?string $from, ?string $to): Comparison
    {
        return new Comparison($to ?? $this->defaultBranch(), $from ?? $this->releases()->latest());
    }

    public function defaultBranch(): string
    {
        return $this->raw['default_branch'];
    }

    public function isFork(): bool
    {
        return $this->raw['fork'];
    }

    public function parent(): ?self
    {
        return isset($this->raw['parent']) ? new self($this->raw['parent'], $this->api) : null;
    }

    public function releases(): ReleaseCollection
    {
        return $this->api->releases($this);
    }
}
