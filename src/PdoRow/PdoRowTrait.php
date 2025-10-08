<?php

namespace Thor\Database\PdoTable\PdoRow;

use ReflectionException;
use JetBrains\PhpStorm\Pure;
use JetBrains\PhpStorm\ArrayShape;
use Thor\Database\PdoTable\PdoRow\Attributes\{Index};
use Thor\Database\PdoTable\PdoRow\Attributes\Table;
use Thor\Database\PdoTable\PdoRow\Attributes\Column;

/**
 * Trait PdoRowTrait: implements RowInterface using PHP 8 attributes.
 *
 * Provides default implementations for schema metadata discovery and
 * hydration/serialization of rows using Column/Index/Table attributes.
 *
 * @package          Thor/Database/PdoTable
 *
 * @since            2020-10
 * @version          1.0
 * @author           Trehinos
 * @copyright        Author
 * @license          MIT
 * @implements       RowInterface
 */
trait PdoRowTrait
{

    /**
     * Cache of discovered table attributes per row class.
     *
     * @var array<class-string, array{table: Table, columns: array<string, Column>, indexes: array<int, Index>, foreign_keys: array}>
     */
    private static array $tablesAttributes = [];

    /**
     * Former primary key values as loaded from DB (used to detect key changes).
     *
     * @var array<string, scalar|null>
     */
    protected array $formerPrimaries = [];

    /**
     * Construct a row with optional initial primary key values.
     *
     * @param array<string, scalar|null> $primaries Map of primary key column => value.
     */
    public function __construct(
        protected array $primaries = []
    ) {
    }

    /**
     * Gets the declared indexes for this row's table.
     *
     * @return array<int, Index> List of Index attribute instances.
     * @throws ReflectionException
     */
    final public static function getIndexes(): array
    {
        return static::getTableAttributes()['indexes'];
    }

    /**
     * Gets all Thor\Database\PdoTable\Attributes\Pdo* attributes.
     *
     * @return array{table: Table, columns: array<string, Column>, indexes: array<int, Index>, foreign_keys: array}
     * @throws ReflectionException
     */
    #[ArrayShape(['table' => Table::class, 'columns' => 'array', 'indexes' => 'array', 'foreign_keys' => 'array'])]
    public static function getTableAttributes(): array
    {
        return static::$tablesAttributes[static::class] ??= AttributesReader::pdoTableInformation(static::class);
    }

    /**
     * Returns an associative array representation of this object compatible with PDOStatement::fetch().
     *
     * Keys are column names and values are SQL-typed values obtained by converting PHP properties
     * through their corresponding Column TableType.
     *
     * @return array<string, mixed>
     * @throws ReflectionException
     */
    public function toPdoArray(): array
    {
        $pdoArray = [];
        foreach (static::getPdoColumnsDefinitions() as $columnName => $pdoColumn) {
            if (in_array($columnName, static::getPrimaryKeys())) {
                $pdoArray[$columnName] = $pdoColumn->toSql($this->primaries[$columnName] ?? null);
                continue;
            }
            $propertyName = str_replace(' ', '_', $columnName);
            $pdoArray[$columnName] = $pdoColumn->toSql($this->$propertyName ?? null);
        }
        return $pdoArray;
    }

    /**
     * Returns declared column definitions keyed by column name.
     *
     * @return array<string, Column> Map of column name => Column attribute instance.
     * @throws ReflectionException
     */
    final public static function getPdoColumnsDefinitions(): array
    {
        return array_combine(
            array_map(fn(Column $column) => $column->getName(), static::getTableAttributes()['columns']),
            array_values(static::getTableAttributes()['columns'])
        );
    }

    /**
     * @return string[] an array of field name(s).
     *
     * @throws ReflectionException
     */
    final public static function getPrimaryKeys(): array
    {
        return static::getPdoTable()->getPrimaryKeys();
    }

    /**
     * Gets the PdoTable representing the table information of this PdoRowInterface.
     *
     * @throws ReflectionException
     */
    final public static function getPdoTable(): Table
    {
        return static::getTableAttributes()['table'];
    }

    /**
     * Hydrates the object from the provided associative array of column => SQL-typed value.
     *
     * If $fromDb is true, then after hydration the former primary and current primary are equal
     * (getFormerPrimary() === getPrimary()).
     *
     * @param array<string, mixed> $pdoArray Associative array of column => SQL-typed value.
     * @param bool                 $fromDb   Whether the data originates from the database.
     *
     * @return void
     * @throws ReflectionException
     */
    public function fromPdoArray(array $pdoArray, bool $fromDb = false): void
    {
        $this->primaries = [];
        $this->formerPrimaries = [];
        foreach ($pdoArray as $columnName => $columnSqlValue) {
            $phpValue = static::getPdoColumnsDefinitions()[$columnName]->toPhp($columnSqlValue);
            if (in_array($columnName, static::getPrimaryKeys())) {
                $this->primaries[$columnName] = $phpValue;
                if ($fromDb) {
                    $this->formerPrimaries[$columnName] = $phpValue;
                }
                continue;
            }
            $propertyName = str_replace(' ', '_', $columnName);
            $this->$propertyName = $phpValue;
        }
    }

    /**
     * @return array get primary keys in an array of 'column_name' => PHP_value.
     */
    final public function getPrimary(): array
    {
        return $this->primaries;
    }

    /**
     * @return array get primary keys as loaded from DB. Empty if not loaded from DB.
     */
    final public function getFormerPrimary(): array
    {
        return $this->formerPrimaries;
    }

    /**
     * Sets the PdoRowTrait SQL table row primary key  as synced with the DB
     */
    final public function reset(): void
    {
        $this->primaries = $this->formerPrimaries;
    }

    /**
     * @return string get primary keys in a concatenated string.
     */
    #[Pure]
    final public function getPrimaryString(): string
    {
        return implode('-', $this->primaries);
    }

    /**
     * Sets primary key value(s).
     *
     * @param array<string, scalar|null> $primary Map of primary key column => value.
     *
     * @return void
     */
    final public function setPrimary(array $primary): void
    {
        $this->primaries = $primary;
    }

}
