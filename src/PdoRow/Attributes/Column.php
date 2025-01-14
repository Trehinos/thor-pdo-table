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
     * Transforms the $sqlValue to a PHP value with this object callable.
     */
    public function toPhp(mixed $sqlValue): mixed
    {
        return $this->type->toPhpValue($sqlValue);
    }

    /**
     * Transforms the $phpValue to an SQL value with this object callable.
     */
    public function toSql(mixed $phpValue): mixed
    {
        return $this->type->toSqlValue($phpValue);
    }

    /**
     * Gets the defined PHP type.
     */
    public function getPhpType(): string
    {
        return $this->type->phpType();
    }

    /**
     * Returns true if this column is nullable.
     */
    public function isNullable(): bool
    {
        return $this->nullable;
    }

    /**
     * The default value of this column.
     */
    public function getDefault(): mixed
    {
        return $this->defaultValue;
    }

    /**
     * Gets the name of the SQL column.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the defined SQL type.
     */
    public function getSqlType(): string
    {
        return $this->type->sqlType();
    }

}
