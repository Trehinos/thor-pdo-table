<?php

namespace Thor\Database\PdoTable\PdoRow\TableType;

/**
 * Contract for TableType implementations used by Column attributes to convert
 * values between SQL storage and PHP domain types, and to expose declared
 * SQL and PHP type information.
 *
 * Implementations are responsible for:
 * - Declaring the PHP native type they produce/consume (phpType)
 * - Declaring the SQL type mnemonic/definition used in DDL (sqlType)
 * - Converting values fetched from PDO into PHP values (toPhpValue)
 * - Converting PHP values into SQL-storable values (toSqlValue)
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
interface TableTypeInterface
{

    /**
     * Gets the PHP type handled by this TableType.
     *
     * @return string PHP scalar/class type name (e.g. "int", "string", "bool", "array").
     */
    public function phpType(): string;

    /**
     * Gets the SQL type declaration associated with this TableType.
     *
     * @return string SQL type or definition (dialect-agnostic mnemonic), e.g. "INTEGER(10)", "VARCHAR(255)".
     */
    public function sqlType(): string;

    /**
     * Converts a raw SQL value (as returned by PDO) to the corresponding PHP value.
     *
     * @param mixed $sqlValue Raw value retrieved from the database.
     *
     * @return mixed PHP-typed value suitable for the domain model.
     */
    public function toPhpValue(mixed $sqlValue): mixed;

    /**
     * Converts a PHP value to its SQL-storable representation.
     *
     * @param mixed $phpValue PHP-typed value from the domain model.
     *
     * @return mixed Value suitable for binding to PDO.
     */
    public function toSqlValue(mixed $phpValue): mixed;

}
