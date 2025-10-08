<?php

namespace Thor\Database\PdoTable\PdoRow\TableType;

/**
 * TableType for variable-length strings (e.g. VARCHAR(n)).
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
class StringType extends BaseType
{

    /**
     * Create a string type with the given length and SQL base type.
     *
     * @param int    $size    Maximum string length for the SQL column.
     * @param string $sqlType SQL base type to use (default: 'VARCHAR').
     */
    public function __construct(public readonly int $size = 255, string $sqlType = 'VARCHAR')
    {
        parent::__construct("$sqlType({$this->size})", 'string');
    }

    /**
     * Return the SQL value as a PHP string.
     *
     * @param mixed $sqlValue Raw SQL value.
     *
     * @return string String value.
     */
    public function toPhpValue(mixed $sqlValue): string
    {
        return $sqlValue;
    }

    /**
     * Cast a PHP value to string for SQL storage.
     *
     * @param mixed $phpValue PHP value.
     *
     * @return string String value.
     */
    public function toSqlValue(mixed $phpValue): string
    {
        return "$phpValue";
    }
}
