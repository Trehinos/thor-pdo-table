<?php

namespace Thor\Database\PdoTable\PdoRow\TableType;

/**
 * TableType that stores arrays as JSON strings in SQL.
 *
 * Note: The resulting SQL type is a variable-length string (e.g. VARCHAR(n)).
 * Decoding is performed with associative arrays.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
class ArrayType extends BaseType
{

    /**
     * Construct an ArrayType with a string-based SQL definition.
     *
     * @param int    $sqlStringSize Maximum size of the SQL string column.
     * @param string $sqlStringType SQL base type to use (e.g. "VARCHAR").
     */
    public function __construct(
        int $sqlStringSize = 4096,
        string $sqlStringType = 'VARCHAR'
    ) {
        parent::__construct("$sqlStringType($sqlStringSize)", 'array');
    }

    /**
     * Decode the JSON string coming from SQL into a PHP array.
     *
     * @param mixed $sqlValue Raw SQL value (JSON string).
     *
     * @return array Decoded associative array.
     */
    public function toPhpValue(mixed $sqlValue): array
    {
        return json_decode($sqlValue, true);
    }

    /**
     * Encode a PHP value as JSON for SQL storage.
     *
     * @param mixed $phpValue PHP array or array-like value.
     *
     * @return string JSON representation.
     */
    public function toSqlValue(mixed $phpValue): string
    {
        return json_encode($phpValue);
    }
}
