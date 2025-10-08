<?php

namespace Thor\Database\PdoTable\PdoRow\Attributes;

use Attribute;


/**
 * Represents a foreign key between two classes describing SQL tables.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
#[Attribute(Attribute::IS_REPEATABLE | Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class ForeignKey
{

    private string $name;

    /**
     * @param class-string $className
     * @param array        $targetColumns
     * @param array        $localColumns
     * @param string|null  $name
     */
    public function __construct(
        private string $className,
        private array $targetColumns,
        private array $localColumns,
        ?string $name = null
    ) {
        $this->name =
            $name ?? 'fk_' . strtolower(basename($this->className) . '_' . implode('_', $this->targetColumns));
    }

    /**
     * Gets the foreign key constraint name.
     *
     * @return string Constraint identifier.
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Gets the bound PdoRowInterface implementor class name.
     *
     * @return class-string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Gets the column names referenced on the target (foreign) table.
     *
     * @return array<int,string> Target column names.
     */
    public function getTargetColumns(): array
    {
        return $this->targetColumns;
    }

    /**
     * Gets the local (current table) column names participating in the relation.
     *
     * @return array<int,string> Local column names.
     */
    public function getLocalColumns(): array
    {
        return $this->localColumns;
    }


}
