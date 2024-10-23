<?php

/*
 * This file is part of the zenstruck/changelog package.
 *
 * (c) Kevin Bond <kevinbond@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zenstruck\Changelog\Github;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Release
{
    protected Repository $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
    }

    final public function __toString(): string
    {
        return $this->name();
    }

    final public function compareFrom(?string $from): Comparison
    {
        return $this->repository->compare($this, $from);
    }

    abstract public function name(): string;

    abstract public function tagName(): string;

    abstract public function body(): string;

    abstract public function url(): string;

    abstract public function publishedAt(): \DateTimeImmutable;

    abstract public function isPreRelease(): bool;

    abstract public function target(): string;
}
