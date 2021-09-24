<?php

namespace Zenstruck\Changelog\Github;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Comparison
{
    private Repository $repository;
    private string $to;
    private ?string $from;
    private CommitCollection $commits;

    public function __construct(Repository $repository, string $to, ?string $from = null)
    {
        $this->repository = $repository;
        $this->to = $to;
        $this->from = $from;
    }

    public function __toString(): string
    {
        return $this->from ? "{$this->from}...{$this->to}" : $this->to;
    }

    public function commits(): CommitCollection
    {
        return $this->commits ??= $this->repository->commits($this)->withoutMerges();
    }

    public function isEmpty(): bool
    {
        return 0 === $this->commits()->count();
    }

    public function to(): string
    {
        return $this->to;
    }

    public function from(): ?string
    {
        return $this->from;
    }

    public function url(): string
    {
        if ($this->from) {
            return "https://github.com/{$this->repository}/compare/{$this}";
        }

        return "https://github.com/{$this->repository}/commits/{$this->to}";
    }
}
