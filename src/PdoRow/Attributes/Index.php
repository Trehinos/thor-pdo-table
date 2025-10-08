<?php

namespace Thor\Database\PdoTable\PdoRow\Attributes;

use Attribute;
use JetBrains\PhpStorm\Pure;

/**
 * Represents an table index.
 *
 * @package Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license MIT
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class Index
{

    private string $name;

    /**
     * @param array       $columnNames
     * @param bool        $isUnique
     * @param string|null $name
     */
    public function __construct(
        private array $columnNames,
        private bool $isUnique = false,
        ?string $name = null
    ) {
        $this->name = $name ??
            (($this->isUnique ? 'uniq_' : 'index_') . strtolower(implode('_', $this->columnNames)));
    }

    /**
     * Gets the index name.
     *
     * @return string Constraint/index identifier.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the column names that compose this index.
     *
     * @return array<int,string> Column names in index order.
     */
    public function getColumnNames(): array
    {
        return $this->columnNames;
    }

    /**
     * Indicates whether this index enforces uniqueness.
     *
     * @return bool True for UNIQUE index, false for a non-unique index.
     */
    public function isUnique(): bool
    {
        return $this->isUnique;
    }

    /**
     * Builds a SQL fragment representing this index definition.
     * Note: The exact emission may be adapted by drivers; this is a generic form.
     *
     * @return string SQL fragment like: "CONSTRAINT [UNIQUE] INDEX name (col1, col2)".
     */
    #[Pure]
    public function getSql(): string
    {
        $unq = $this->isUnique() ? ' UNIQUE' : '';
        $cols = implode(', ', $this->getColumnNames());

        return "CONSTRAINT$unq INDEX {$this->getName()} ($cols)";
    }


}
