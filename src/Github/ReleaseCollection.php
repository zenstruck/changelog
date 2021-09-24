<?php

namespace Zenstruck\Changelog\Github;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ReleaseCollection implements \IteratorAggregate
{
    private Repository $repository;
    private array $data;

    public function __construct(Repository $repository, array $data)
    {
        $this->repository = $repository;
        $this->data = $data;
    }

    public function create(string $target, string $name, string $body, bool $preRelease = false): Release
    {
        return new Release($this->repository->api()->request(
            'POST',
            "/repos/{$this->repository}/releases",
            [
                'json' => [
                    'name' => $name,
                    'target_commitish' => $target,
                    'tag_name' => $name,
                    'prerelease' => $preRelease,
                    'body' => $body,
                ],
            ]
        ));
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
