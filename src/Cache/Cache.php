<?php

namespace Thor\Database\PdoTable\Cache;

use Thor\Database\PdoExtension\Criteria;
use Thor\Database\PdoTable\CrudHelper;

/**
 * @template T
 */
final class Cache
{

    /** @var array<string, CachedEntry<T>> */
    private array $cache;

    public function __construct(private readonly CrudHelper $crud)
    {
        $this->cache = [];
    }

    /**
     * @param string $key
     *
     * @return T|null
     */
    public function get(string $key): mixed
    {
        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key]->value;
        }
        $player = $this->crud->readOne(['id' => $key]);
        if ($player === null) {
            return null;
        }
        $this->cache[$key] = CachedEntry::sync($player);
        return $player;
    }

    /**
     * @param string $key
     * @param T $value
     *
     * @return void
     */
    public function set(string $key, mixed $value): void
    {
        $this->cache[$key] = CachedEntry::pending($value);
    }

    /**
     * @return array<CachedEntry<T>>
     */
    public function getPending(): array
    {
        return array_filter($this->cache, fn(CachedEntry $entry) => !$entry->synchronized());
    }

    public function load(array $list): void {
        foreach ($list as $player) {
            $this->cache[$player['id']] = CachedEntry::sync($player);
        }
    }

    public function loadAll(): void
    {
        $this->load($this->crud->listAll());
    }

    public function loadList(Criteria $criteria): void {
        $this->load($this->crud->readMultipleBy($criteria));
    }

    public function clear(): void
    {
        $this->cache = [];
    }

    public function persistAll(): void
    {
        foreach ($this->getPending() as $entry) {
            $this->crud->updateOne($entry->value);
            $entry->persist();
        }
    }

}
