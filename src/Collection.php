<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
abstract class Collection implements \IteratorAggregate, \Countable
{
    final public function isEmpty(): bool
    {
        return 0 === $this->count();
    }
}
