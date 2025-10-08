<?php

namespace Thor\Database\PdoTable\PdoRow\TableType;

/**
 * TableType for integer values stored in INTEGER(n) columns.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
class IntegerType extends BaseType
{

    /**
     * Create an IntegerType with a given display size.
     *
     * @param int $size Size hint for the SQL INTEGER definition (e.g. 10 -> INTEGER(10)).
     */
    public function __construct(public readonly int $size = 10)
    {
        parent::__construct("INTEGER({$this->size})", 'int');
    }

    /**
     * Cast the SQL value to an integer for PHP.
     *
     * @param mixed $sqlValue Raw SQL value.
     *
     * @return int PHP integer.
     */
    public function toPhpValue(mixed $sqlValue): int
    {
        return $sqlValue;
    }

    /**
     * Cast a PHP value to integer for SQL storage.
     *
     * @param mixed $phpValue PHP value to cast to int.
     *
     * @return int Integer value.
     */
    public function toSqlValue(mixed $phpValue): int
    {
        return (int)$phpValue;
    }

}
