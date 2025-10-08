<?php

namespace Thor\Database\PdoTable\Cache;

/**
 * Wrapper for a cached value with a synchronization flag.
 *
 * Used internally by Cache to track whether a value has pending changes
 * to be persisted via the CrudHelper.
 *
 * @template T
 */
final class CachedEntry {

    /**
     * @param T $value
     * @param bool $synced
     */
    private function __construct(
        public mixed $value,
        private bool  $synced,
    ) {}

    /**
     * @param T $value
     * @return CachedEntry
     */
    public static function pending(mixed $value): CachedEntry {
        return new self($value, false);
    }

    /**
     * @param T $value
     * @return CachedEntry
     */
    public static function sync(mixed $value): CachedEntry {
        return new self($value, true);
    }

    /**
     * Whether the entry is synchronized with the database.
     *
     * @return bool True if no pending changes exist.
     */
    public function synchronized(): bool {
        return $this->synced;
    }

    /**
     * Mark the entry as having pending changes.
     *
     * Fluent method returning the same instance.
     *
     * @return $this
     */
    public function update(): self {
        $this->synced = false;
        return $this;
    }

    /**
     * Mark the entry as synchronized (no pending changes).
     *
     * Fluent method returning the same instance.
     *
     * @return $this
     */
    public function persist(): self {
        $this->synced = true;
        return $this;
    }

}
