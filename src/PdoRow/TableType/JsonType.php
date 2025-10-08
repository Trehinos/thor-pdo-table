<?php

namespace Thor\Database\PdoTable\PdoRow\TableType;

/**
 * TableType for generic JSON data stored as strings in SQL.
 *
 * Decodes using json_decode with configurable associative flag.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
class JsonType extends BaseType
{

    /**
     * Construct a JSON TableType with a string-based SQL definition.
     *
     * @param int    $sqlStringSize Maximum SQL string size.
     * @param string $sqlStringType SQL base type (e.g. 'VARCHAR').
     * @param bool   $associative   Whether to decode JSON as associative arrays (true) or objects (false).
     */
    public function __construct(
        int                   $sqlStringSize = 16384,
        string                $sqlStringType = 'VARCHAR',
        private readonly bool $associative = true,
    ) {
        parent::__construct("$sqlStringType($sqlStringSize)", 'int');
    }

    /**
     * Decode the JSON string from SQL into a PHP value.
     *
     * @param mixed $sqlValue Raw JSON string from SQL.
     *
     * @return array Decoded value (array when associative=true, object/array otherwise).
     */
    public function toPhpValue(mixed $sqlValue): array
    {
        return json_decode($sqlValue, $this->associative);
    }

    /**
     * Encode a PHP value as a JSON string for SQL.
     *
     * @param mixed $phpValue Any JSON-serializable PHP value.
     *
     * @return string JSON representation.
     */
    public function toSqlValue(mixed $phpValue): string
    {
        return json_encode($phpValue);
    }
}
