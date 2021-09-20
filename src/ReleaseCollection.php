<?php

namespace Zenstruck\Changelog;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ReleaseCollection implements \IteratorAggregate
{
    private array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function latest(): ?Release
    {
        foreach ($this as $release) {
            return $release;
        }

        return null;
    }

    /**
     * @return \Traversable|Release[]
     */
    public function getIterator(): \Traversable
    {
        foreach ($this->data as $item) {
            yield new Release($item);
        }
    }
}
