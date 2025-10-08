<?php

namespace Thor\Database\PdoTable\PdoRow\Attributes;

use Attribute;
use Thor\Database\PdoTable\PdoRow\TableType\TableTypeInterface;

/**
 * Describe a PdoColumn attribute. Use this attribute on a PdoRowInterface implementor
 * to specify a column from which read and which to write data in the database.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Column
{

    /**
     * @param string             $name
     * @param TableTypeInterface $type
     * @param bool               $nullable
     * @param mixed|null         $defaultValue
     */
    public function __construct(
        private string $name,
        private TableTypeInterface $type,
        private bool $nullable = true,
        private mixed $defaultValue = null,
    ) {
    }

    /**
     * Converts a raw SQL value coming from the database into the corresponding PHP value
     * according to this column's TableType.
     *
     * @param mixed $sqlValue Raw value as fetched from PDO/SQL.
     *
     * @return mixed PHP-typed value ready to be used in the domain model.
     */
    public function toPhp(mixed $sqlValue): mixed
    {
        return $this->type->toPhpValue($sqlValue);
    }

    /**
     * Converts a PHP value to the corresponding SQL-storable representation
     * according to this column's TableType.
     *
     * @param mixed $phpValue PHP-typed value from the domain model.
     *
     * @return mixed Value suitable for binding to PDO.
     */
    public function toSql(mixed $phpValue): mixed
    {
        return $this->type->toSqlValue($phpValue);
    }

    /**
     * Gets the native PHP type for values of this column.
     *
     * @return string PHP scalar/class type as declared by the TableType.
     */
    public function getPhpType(): string
    {
        return $this->type->phpType();
    }

    /**
     * Indicates whether this column accepts NULL values.
     *
     * @return bool True if the column is nullable; false otherwise.
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * Gets the default value of this column when none is provided.
     *
     * @return mixed|null The default value or null when not defined.
     */
    public function getDefault(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Gets the SQL column identifier (name).
     *
     * @return string Column name as used in the database schema.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the SQL type declaration associated with this column.
     *
     * @return string SQL type (dialect-independent mnemonic) from the TableType.
     */
    public function getSqlType(): string
    {
        return $this->type->sqlType();
    }

}
