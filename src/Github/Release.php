<?php

namespace Zenstruck\Changelog\Github;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Release
{
    final public function __toString(): string
    {
        return $this->name();
    }

    abstract public function name(): string;

    abstract public function tagName(): string;

    abstract public function body(): string;

    abstract public function url(): string;

    abstract public function publishedAt(): \DateTimeImmutable;

    abstract public function isPreRelease(): bool;

    abstract public function compareFrom(?string $from): Comparison;
}
