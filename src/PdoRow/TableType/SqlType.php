<?php

namespace Thor\Database\PdoTable\PdoRow\TableType;

/**
 * Generic string-backed SQL type passthrough.
 *
 * Useful when you simply want a specific SQL type while keeping PHP values as strings.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
class SqlType extends BaseType
{

    /**
     * Define the SQL type while keeping PHP as string.
     *
     * @param string $sqlType SQL type/definition (default: 'VARCHAR').
     */
    public function __construct(string $sqlType = 'VARCHAR')
    {
        parent::__construct("$sqlType", 'string');
    }

    /**
     * Cast the SQL scalar to string.
     *
     * @param mixed $sqlValue Raw SQL value.
     *
     * @return string String value.
     */
    public function toPhpValue(mixed $sqlValue): string
    {
        return "$sqlValue";
    }

    /**
     * Cast the PHP value to string for SQL storage.
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
