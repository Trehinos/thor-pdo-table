<?php

namespace Thor\Database\PdoTable\PdoRow\Attributes;

use Attribute;

/**
 * Describe a PdoTable attribute. Use this attribute on a PdoRowInterface implementor
 * to specify the corresponding table name, primary keys and auto-increment.
 *
 * @package Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license MIT
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Table
{

    /**
     * @param string|null $tableName
     * @param array       $primary
     * @param string|null $auto
     */
    public function __construct(
        private ?string $tableName = null,
        private array $primary = [],
        private ?string $auto = null
    ) {
    }

    /**
     * Returns the SQL table name.
     *
     * @return string|null Explicit table name or null to derive it from the class name.
     */
    public function getTableName(): ?string
    {
        return $this->tableName;
    }

    /**
     * Returns the primary key column names.
     *
     * @return array<int,string> List of primary key columns in order.
     */
    public function getPrimaryKeys(): array
    {
        return $this->primary;
    }

    /**
     * Returns the auto-increment column name if any.
     *
     * @return string|null Auto-increment column or null when not applicable.
     */
    public function getAutoColumnName(): ?string
    {
        return $this->auto;
    }

}
