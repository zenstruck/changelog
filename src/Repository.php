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

    public function defaultBranch(): string
    {
        return $this->raw['default_branch'];
    }

    public function source(): ?self
    {
        return isset($this->raw['source']) ? new self($this->raw['source'], $this->api) : null;
    }

    public function releases(): ReleaseCollection
    {
        return $this->api->releases($this);
    }
}
