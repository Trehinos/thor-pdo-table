<?php

namespace Thor\Database\PdoTable\PdoRow;

use Thor\Database\PdoTable\PdoRow\Attributes\Table;

/**
 * Represents a class describing an SQL table.
 *
 * The class implementing this interface can then be used with CrudHelper or SchemaHelper.
 *
 * Use PdoRowTrait, AbstractPdoRow or BasePdoRow to implement easily all methods and use with PdoTable\Attributes.
 *
 * @see              PdoRowTrait
 * @see              AbstractRow
 * @see              Row
 * @see              CrudHelper
 * @see              SchemaHelper
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
interface RowInterface
{

    /**
     * Gets the PdoTable attribute representing the table information of this row class.
     *
     * @return Table Table attribute carrying table name, primary keys and auto-increment column.
     */
    public static function getPdoTable(): Table;

    /**
     * Returns the declared columns definitions of the row class.
     *
     * @return array<string, mixed> Map of column name => Column attribute instance.
     */
    public static function getPdoColumnsDefinitions(): array;

    /**
     * Returns the primary key column names.
     *
     * @return array<int,string> Ordered list of primary key column names.
     */
    public static function getPrimaryKeys(): array;

    /**
     * Returns the declared indexes for the table represented by this row class.
     *
     * @return array<int, mixed> List of Index attribute instances.
     */
    public static function getIndexes(): array;

    /**
     * Returns an array representation of this object which matches PDOStatement::fetch() output.
     *
     * @return array<string, mixed> Associative array of column => SQL-typed value.
     */
    public function toPdoArray(): array;

    /**
     * Hydrates the object from a PDO-like associative array.
     * If $fromDb is true, the loaded primary keys are copied to former primary keys.
     *
     * @param array<string, mixed> $pdoArray Associative array of column => SQL-typed value.
     * @param bool                 $fromDb   Whether the data comes from the database (synchronizes former primary).
     *
     * @return void
     */
    public function fromPdoArray(array $pdoArray, bool $fromDb = false): void;

    /**
     * Copy formerPrimary on primary array.
     *
     * @return void
     */
    public function reset(): void;

    /**
     * Get primary keys in an array of 'column_name' => PHP_value.
     *
     * @return array<string, scalar|null> Primary key column values.
     */
    public function getPrimary(): array;

    /**
     * Get primary keys as loaded from DB. Empty if not loaded from DB.
     *
     * @return array<string, scalar|null> Former primary key column values.
     */
    public function getFormerPrimary(): array;

    /**
     * Get primary keys in a concatenated string.
     *
     * @return string Concatenation of primary key values joined by '-'.
     */
    public function getPrimaryString(): string;

}
