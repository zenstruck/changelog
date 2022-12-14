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
final class PullRequestCollection implements \IteratorAggregate
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function first(): ?PullRequest
    {
        foreach ($this as $pullRequest) {
            return $pullRequest;
        }

        return null;
    }

    /**
     * @return \Traversable|PullRequest[]
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->data as $item) {
            yield new PullRequest($item);
        }
    }
}
