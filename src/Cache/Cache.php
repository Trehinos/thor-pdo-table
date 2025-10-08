<?php

namespace Thor\Database\PdoTable\Cache;

use Thor\Database\PdoExtension\Criteria;
use Thor\Database\PdoTable\CrudHelper;

/**
 * In-memory write-back cache for rows handled by a CrudHelper.
 *
 * The cache stores values keyed by their 'id', tracks pending updates, and can persist them in batch.
 *
 * @template T
 */
final class Cache
{

    /** @var array<string, CachedEntry<T>> */
    private array $cache;

    /**
     * Create a new Cache instance bound to a CrudHelper.
     *
     * @param CrudHelper $crud CRUD helper used to load, read and persist rows.
     */
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

    /**
     * Preload a list of rows into the cache as synchronized entries.
     *
     * The array must contain associative arrays indexed by column names and include an 'id' key.
     *
     * @param array $list List of rows as associative arrays coming from the database.
     */
    public function load(array $list): void
    {
        foreach ($list as $player) {
            $this->cache[$player['id']] = CachedEntry::sync($player);
        }
    }

    /**
     * Load all rows from the underlying CRUD helper into the cache.
     *
     * All cached entries will be marked as synchronized.
     */
    public function loadAll(): void
    {
        $this->load($this->crud->listAll());
    }

    /**
     * Load rows matching the given criteria into the cache.
     *
     * @param Criteria $criteria Selection criteria used by the CrudHelper.
     */
    public function loadList(Criteria $criteria): void
    {
        $this->load($this->crud->readMultipleBy($criteria));
    }

    /**
     * Clear all cached entries (both synchronized and pending changes).
     */
    public function clear(): void
    {
        $this->cache = [];
    }

    /**
     * Persist all pending entries to the database and mark them as synchronized.
     */
    public function persistAll(): void
    {
        foreach ($this->getPending() as $entry) {
            $this->crud->updateOne($entry->value);
            $entry->persist();
        }
    }

}
