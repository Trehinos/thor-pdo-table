<?php

namespace Thor\Database\PdoTable\PdoRow\TableType;

/**
 * Convenience abstract implementation of TableTypeInterface providing
 * storage of the declared SQL and PHP type names.
 *
 * Concrete subclasses only need to implement value conversions.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
abstract class BaseType implements TableTypeInterface
{

    /**
     * Construct a TableType with its SQL and PHP type descriptors.
     *
     * @param string $sqlType SQL type/definition to be used in DDL (e.g. "VARCHAR(255)").
     * @param string $phpType PHP type name produced/consumed by this TableType (e.g. "string").
     */
    public function __construct(
        protected readonly string $sqlType,
        protected readonly string $phpType,
    ) {
    }

    /**
     * Gets the PHP type handled by this TableType.
     *
     * @return string PHP scalar/class type name.
     */
    public function phpType(): string
    {
        return $this->phpType;
    }

    /**
     * Gets the SQL type declaration.
     *
     * @return string SQL type or definition.
     */
    public function sqlType(): string
    {
        return $this->sqlType;
    }

    /**
     * Convert value as fetched from SQL to PHP.
     *
     * @param mixed $sqlValue Raw value retrieved from the database.
     *
     * @return mixed PHP-typed value.
     */
    abstract public function toPhpValue(mixed $sqlValue): mixed;

    /**
     * Convert value from PHP to a SQL-storable representation.
     *
     * @param mixed $phpValue PHP-typed value.
     *
     * @return mixed Value suitable for binding to PDO.
     */
    abstract public function toSqlValue(mixed $phpValue): mixed;

}
