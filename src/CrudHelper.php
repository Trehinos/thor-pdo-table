<?php

/**
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */

namespace Thor\Database\PdoTable;

use Exception;
use TypeError;
use JetBrains\PhpStorm\Pure;
use Thor\Database\PdoExtension\Criteria;
use Thor\Database\PdoExtension\Requester;
use Thor\Database\PdoExtension\ArrayCrud;
use Thor\Database\PdoTable\PdoRow\AbstractRow;
use Thor\Database\PdoTable\PdoRow\RowException;
use Thor\Database\PdoTable\PdoRow\RowInterface;

/**
 * Class CrudHelper : SQL CRUD operation requester for PdoRows.
 *
 * @template T of RowInterface
 * @package  Thor\Database\PdoTable
 */
final class CrudHelper implements CrudInterface
{

    private ArrayCrud $arrayCrud;
    private array     $primary;

    /**
     * CrudHelper constructor.
     * Creates a new CRUD requester to manage PdoRows
     *
     * @param class-string<T> $className which implements PdoRowInterface and use PdoRowTrait trait.
     */
    public function __construct(
        private readonly string $className,
        Requester $requester,
        array $insertExcludedColumns = [],
        array $updateExcludedColumns = []
    ) {
        if (!class_exists($this->className) || !in_array(RowInterface::class, class_implements($this->className))) {
            throw new TypeError("{$this->className} class not found or not implementing PdoRowInterface...");
        }
        $tableName = ($this->className)::getPdoTable()->getTableName();
        $this->primary = ($this->className)::getPrimaryKeys();
        $this->arrayCrud = new ArrayCrud(
            $tableName,
            $this->primary,
            $requester,
            $insertExcludedColumns,
            $updateExcludedColumns
        );
    }

    public function getRequester(): Requester
    {
        return $this->arrayCrud->getRequester();
    }

    /**
     * List all rows of the entity managed by this Crud.
     *
     * @return T[]
     */
    public function listAll(): array
    {
        return array_map(
            fn(array $row) => self::instantiateFromRow($this->className, $row, true),
            $this->arrayCrud->listAll()
        );
    }

    /**
     * @inheritDoc
     */
    #[Pure]
    public function table(): string
    {
        return $this->arrayCrud->table();
    }

    /**
     * Create one row in DB.
     *
     * @param RowInterface|T $row
     *
     * @return string primary string/key
     *
     * @throws Exception
     */
    public function createOne(object $row): string
    {
        if (!empty($row->getFormerPrimary())) {
            throw new RowException(
                "PdoRow with primary string '{$row->getPrimaryString()}' cannot be inserted as it has been loaded from DB."
            );
        }
        $primaryString = $this->arrayCrud->createOne($row->toPdoArray());
        if ($row instanceof AbstractRow) {
            return $row->getPublicId();
        }
        return $primaryString;
    }

    /**
     * Creates multiple rows in DB.
     *
     * @param T[] $rows
     *
     * @return bool
     *
     * @throws Exception
     */
    public function createMultiple(array $rows): bool
    {
        return $this->arrayCrud->createMultiple(array_map(fn(RowInterface $row) => $row->toPdoArray(), $rows));
    }

    /**
     * Gets one row from its primary keys.
     *
     * Returns `null` if the row has not been found.
     *
     * @param array $primaries
     *
     * @return T|null
     */
    public function readOne(array $primaries): ?object
    {
        return $this->readOneBy($this->primaryArrayToCriteria($primaries));
    }

    /**
     * Gets one/some/all column(s) of one row.
     *
     * Returns `null` if the row has not been found.
     *
     * @param Criteria $criteria
     * @param array|string|null $columns
     *
     * @return ?array
     */
    public function read(Criteria $criteria, array|string|null $columns = null): ?array
    {
        return $this->arrayCrud->read($criteria, $columns);
    }

    /**
     * Gets one row from the specified criteria.
     *
     * Returns `null` if the row has not been found.
     *
     * @param Criteria $criteria
     *
     * @return T|null
     */
    public function readOneBy(Criteria $criteria): ?object
    {
        $row = $this->arrayCrud->read($criteria);
        if (empty($row)) {
            return null;
        }

        return self::instantiateFromRow($this->className, $row, true);
    }

    /**
     * Gets multiple rows from the specified criteria.
     *
     * @param Criteria $criteria
     *
     * @return T[]
     */
    public function readMultipleBy(Criteria $criteria): array
    {
        return array_map(
            fn(array $row) => self::instantiateFromRow($this->className, $row, true),
            $this->arrayCrud->readMultipleBy($criteria)
        );
    }

    /**
     * Updates all column of the corresponding row in DB.
     *
     * Returns true if the statement has correctly been executed.
     *
     * @param T $row
     *
     * @return bool
     */
    public function updateOne(object $row): bool
    {
        $criteria = $this->primaryArrayToCriteria($row->getPrimary());
        return $this->arrayCrud->update($row->toPdoArray(), $criteria);
    }

    /**
     * Delete the corresponding row in DB.
     *
     * Returns true if the statement has correctly been executed.
     *
     * @param T $row
     *
     * @return bool
     */
    public function deleteOne(object $row): bool
    {
        $criteria = $this->primaryArrayToCriteria($row->getPrimary());
        return $this->arrayCrud->delete($criteria);
    }

    /**
     * @param class-string<T> $className
     *
     * @return T
     */
    public static function instantiateFromRow(
        string $className,
        array $row,
        bool $fromDb = false,
        mixed ...$constructorArguments
    ): RowInterface {
        $rowObj = new $className(...$constructorArguments);
        $rowObj->fromPdoArray($row, $fromDb);
        return $rowObj;
    }

    /**
     * @param array $primaries
     *
     * @return Criteria
     */
    public function primaryArrayToCriteria(array $primaries): Criteria
    {
        $criteria = [];
        foreach ($this->primary as $primaryKey) {
            $criteria[$primaryKey] = array_shift($primaries);
        }

        return new Criteria($criteria);
    }

}
