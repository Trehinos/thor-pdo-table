<?php

namespace Thor\Database\PdoTable\Cache;

/**
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

    public function synchronized(): bool {
        return $this->synced;
    }

    public function update(): self {
        $this->synced = false;
        return $this;
    }

    public function persist(): self {
        $this->synced = true;
        return $this;
    }

}
