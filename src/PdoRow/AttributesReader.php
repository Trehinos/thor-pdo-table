<?php

namespace Thor\Database\PdoTable\PdoRow;

use ReflectionClass;
use ReflectionAttribute;
use ReflectionException;
use JetBrains\PhpStorm\Pure;
use JetBrains\PhpStorm\ArrayShape;
use Thor\Database\PdoTable\PdoRow\Attributes\Table;
use Thor\Database\PdoTable\PdoRow\Attributes\Index;
use Thor\Database\PdoTable\PdoRow\Attributes\Column;
use Thor\Database\PdoTable\PdoRow\Attributes\ForeignKey;

/**
 * Utility to reflect and aggregate PHP 8 attributes that describe a RowInterface mapping.
 *
 * This reader walks through a class, its used traits, and its parent classes to collect
 * Table, Column, Index and ForeignKey attributes, merging them according to precedence
 * (child overrides/extends parent and traits contribute as if mixed into the class).
 *
 * Cached results are kept per-class within this process to avoid repeated reflection.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
final class AttributesReader
{

    #[ArrayShape(['table' => Table::class, 'columns' => 'array', 'indexes' => 'array', 'foreign_keys' => 'array'])]
    /**
     * In-process cache of parsed attributes keyed by class name.
     *
     * @var array<class-string, array{table: Table|null, columns: array<int, Column>, indexes: array<int, Index>, foreign_keys: array<int, ForeignKey>}> $classInfos
     */
    private static array $classInfos = [];

    /**
     * Create an attributes reader bound to a specific RowInterface class name.
     *
     * No reflection work is performed here; call getAttributes() to parse and cache
     * the metadata for the provided class name.
     *
     * @param class-string $classname Fully-qualified class name implementing RowInterface to inspect.
     */
    public function __construct(private string $classname)
    {
    }

    /**
     * Parse attributes on the given reflected class and aggregate them with attributes
     * inherited from used traits and the parent class hierarchy.
     *
     * Precedence: values defined on the current class take precedence over values coming
     * from traits and parents. Columns, indexes and foreign keys are appended in order.
     *
     * @param ReflectionClass $rc Reflected class to inspect.
     *
     * @return array{table: Table|null, columns: array<int, Column>, indexes: array<int, Index>, foreign_keys: array<int, ForeignKey>} Aggregated attributes for the class.
     */
    #[ArrayShape(['table' => Table::class, 'columns' => 'array', 'indexes' => 'array', 'foreign_keys' => 'array'])]
    private static function parseAttributes(
        ReflectionClass $rc
    ): array {
        /** @var Table $table */
        $table = ($rc->getAttributes(Table::class)[0] ?? null)?->newInstance();
        $columns = array_map(
            fn(ReflectionAttribute $ra) => $ra->newInstance(),
            $rc->getAttributes(Column::class)
        );
        /** @var Index[] $indexes */
        $indexes = array_map(
            fn(ReflectionAttribute $ra) => $ra->newInstance(),
            $rc->getAttributes(Index::class)
        );
        /** @var ForeignKey[] $fks */
        $fks = array_map(
            fn(ReflectionAttribute $ra) => $ra->newInstance(),
            $rc->getAttributes(ForeignKey::class)
        );

        foreach ($rc->getTraits() as $t) {
            ['table' => $pTable, 'columns' => $pColumns, 'indexes' => $pIndexes, 'foreign_keys' => $pFks] =
                self::parseAttributes($t);
            ['table' => $table, 'columns' => $columns, 'indexes' => $indexes] =
                self::_merge($pTable, $table, $pColumns, $columns, $pIndexes, $indexes, $fks, $pFks);
        }

        if ($p = $rc->getParentClass()) {
            ['table' => $pTable, 'columns' => $pColumns, 'indexes' => $pIndexes, 'foreign_keys' => $pFks] =
                self::parseAttributes($p);
            ['table' => $table, 'columns' => $columns, 'indexes' => $indexes] =
                self::_merge($pTable, $table, $pColumns, $columns, $pIndexes, $indexes, $fks, $pFks);
        }

        return ['table' => $table, 'columns' => $columns, 'indexes' => $indexes, 'foreign_keys' => $fks];
    }

    /**
     * Merge two attribute sets coming from parent/trait (A) and current class (B).
     *
     * - Table: when A has a table, it is combined with B so that B's name/auto override A's,
     *   and primary key arrays are concatenated (A then B) to preserve ordering.
     * - Columns/Indexes/Foreign keys: arrays are concatenated keeping original order (A then B).
     *
     * @param Table|null $tableA   Table attribute from parent/trait.
     * @param Table|null $tableB   Table attribute from current class.
     * @param array      $columnsA Column attributes from parent/trait.
     * @param array      $columnsB Column attributes from current class.
     * @param array      $indexA   Index attributes from parent/trait.
     * @param array      $indexB   Index attributes from current class.
     * @param array      $fkA      ForeignKey attributes from parent/trait.
     * @param array      $fkB      ForeignKey attributes from current class.
     *
     * @return array{table: Table|null, columns: array<int, Column>, indexes: array<int, Index>, foreign_keys: array<int, ForeignKey>} The merged attribute set.
     */
    #[Pure]
    #[ArrayShape(['table' => Table::class, 'columns' => 'array', 'indexes' => 'array', 'foreign_keys' => 'array'])]
    private static function _merge(
        ?Table $tableA,
        ?Table $tableB,
        array $columnsA,
        array $columnsB,
        array $indexA,
        array $indexB,
        array $fkA,
        array $fkB
    ): array {
        return [
            'table' => ($tableA === null) ? $tableB :
                new Table(
                    $tableB?->getTableName() ?? $tableA->getTableName(),
                    array_merge($tableA->getPrimaryKeys(), $tableB?->getPrimaryKeys() ?? []),
                    $tableB?->getAutoColumnName() ?? $tableA->getAutoColumnName(),
                )
            ,
            'columns' => array_merge($columnsA, $columnsB),
            'indexes' => array_merge($indexA, $indexB),
            'foreign_keys' => array_merge($fkA, $fkB)
        ];
    }

    /**
     * Returns the aggregated attributes for the class bound to this reader.
     *
     * Results are cached in-process. Subsequent calls for the same class name will
     * return the cached array without additional reflection overhead.
     *
     * @return array{table: Table|null, columns: array<int, Column>, indexes: array<int, Index>, foreign_keys: array<int, ForeignKey>} Aggregated attributes for the class.
     * @throws ReflectionException When the provided class name cannot be reflected.
     */
    #[ArrayShape(['table' => Table::class, 'columns' => 'array', 'indexes' => 'array', 'foreign_keys' => 'array'])]
    public function getAttributes(): array
    {
        return self::$classInfos[$this->classname] ??= self::parseAttributes(new ReflectionClass($this->classname));
    }

    /**
     * Returns the aggregated attributes for the specified RowInterface class.
     *
     * This is a convenience shortcut that constructs a reader and delegates to getAttributes().
     *
     * @param class-string $className Fully-qualified class name implementing RowInterface to inspect.
     *
     * @return array{table: Table|null, columns: array<int, Column>, indexes: array<int, Index>, foreign_keys: array<int, ForeignKey>} Aggregated attributes for the class.
     * @throws ReflectionException When the provided class name cannot be reflected.
     */
    #[ArrayShape(['table' => Table::class, 'columns' => 'array', 'indexes' => 'array', 'foreign_keys' => 'array'])]
    public static function pdoTableInformation(string $className): array
    {
        return (new self($className))->getAttributes();
    }

}

