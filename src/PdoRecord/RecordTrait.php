<?php

namespace Thor\Database\PdoTable\PdoRecord;

use Thor\Database\PdoExtension\Criteria;
use Thor\Database\PdoExtension\Requester;
use Thor\Database\PdoTable\CrudHelper;
use Thor\Database\PdoTable\Driver\DriverInterface;
use Thor\Database\PdoTable\SchemaHelper;
use Thor\Database\PdoTable\PdoRow\RowInterface;

/**
 * Trait providing high-level Record behavior on top of a PdoRow.
 *
 * It adds convenience operations such as insert, update, upsert, delete,
 * table creation/drop, and synchronization helpers leveraging CrudHelper and
 * SchemaHelper. It also tracks whether the in-memory object is synchronized
 * with the corresponding database row and whether it exists in the database.
 *
 * @implements RecordInterface
 * @implements RowInterface
 */
trait RecordTrait
{

    /**
     * Whether the in-memory object matches the current database state.
     */
    protected bool $isSynced = false;
    /**
     * Whether a corresponding row exists in the database.
     */
    protected bool $existsInDatabase = false;
    /**
     * Whether this object currently holds no data (e.g., constructed without primaries).
     */
    protected bool $objectEmpty = true;

    /**
     * Construct the trait with its RecordManager dependency.
     *
     * The RecordManager wires a CrudHelper and a SchemaHelper that this trait
     * uses to perform database operations and DDL management.
     *
     * @param RecordManager $manager Aggregates CrudHelper and SchemaHelper for this record.
     */
    public function __construct(private readonly RecordManager $manager)
    {
    }

    /**
     * Convenience factory to instantiate and wire a Record using a driver and requester.
     *
     * @param DriverInterface $driver SQL dialect driver used by SchemaHelper.
     * @param Requester $requester PDO requester used by CrudHelper operations.
     * @param mixed ...$args Additional constructor arguments forwarded to the concrete class.
     *
     * @return static A new instance already bound to a CrudHelper and SchemaHelper.
     */
    public static function load(DriverInterface $driver, Requester $requester, mixed ...$args): static
    {
        return new static(RecordManager::create($driver, $requester, static::class), ...$args);
    }

    /**
     * Get the CRUD helper bound to this record.
     *
     * @return CrudHelper Helper used to perform create/read/update/delete operations.
     */
    final public function getCrudHelper(): CrudHelper
    {
        return $this->manager->crud;
    }

    /**
     * Get the schema helper bound to this record.
     *
     * @return SchemaHelper Helper used to generate and manage table/index DDL.
     */
    final public function getSchemaHelper(): SchemaHelper
    {
        return $this->manager->schema;
    }

    /**
     * Create the underlying SQL table for the record using the configured driver.
     *
     * @return bool True if the DDL executed successfully.
     */
    public function createTable(): bool
    {
        return $this->getSchemaHelper()->createTable();
    }

    /**
     * Whether this in-memory object is synchronized with its database row.
     *
     * - Returns true if it exists in DB and fields match the stored values.
     * - Returns false if it exists in DB but has local changes.
     * - Returns null if the object is empty or no DB row exists.
     */
    public function synced(): ?bool
    {
        return !$this->objectEmpty ? $this->existsInDatabase && $this->isSynced : null;
    }

    /**
     * Insert this record into the database.
     *
     * Does nothing and returns false if the object is empty or already exists in the database.
     *
     * @return bool True on successful insertion.
     */
    public function insert(): bool
    {
        if ($this->objectEmpty || $this->existsInDatabase) {
            return false;
        }
        $this->isSynced = false;
        $ret = $this->getCrudHelper()->createOne($this);
        if ($ret) {
            $this->isSynced = true;
            $this->existsInDatabase = true;
        }
        return $ret;
    }

    /**
     * Update the corresponding row in the database with this object's current values.
     *
     * Does nothing and returns false if the object is empty or does not exist in the database.
     *
     * @return bool True on successful update.
     */
    public function update(): bool
    {
        if ($this->objectEmpty || !$this->existsInDatabase) {
            return false;
        }
        $this->isSynced = false;
        $ret = $this->getCrudHelper()->updateOne($this);
        if ($ret) {
            $this->isSynced = true;
        }
        return $ret;
    }

    /**
     * Insert or update this record depending on its existence in the database.
     *
     * @return bool True on successful insert or update.
     */
    public function upsert(): bool
    {
        return match ($this->existsInDatabase) {
            true => $this->update(),
            false => $this->insert()
        };
    }

    /**
     * Delete the corresponding row from the database.
     *
     * @return bool True if a row was deleted; false if no corresponding row exists.
     */
    public function delete(): bool
    {
        if ($this->existsInDatabase) {
            $this->isSynced = false;
            return $this->getCrudHelper()->deleteOne($this);
        }
        return false;
    }

    /**
     * Refresh this object's data from the database.
     *
     * If no criteria is provided, the primary key of the current object is used.
     * If the object is already marked as synced and no criteria is provided, this
     * method is a no-op returning true.
     *
     * @param Criteria|null $criteria Optional criteria to select the row to reload.
     *
     * @return bool True if data was loaded; false if no row matched.
     */
    public function reload(?Criteria $criteria = null): bool
    {
        if ($criteria === null) {
            if ($this->isSynced) {
                return true;
            }
            $criteria = $this->getCrudHelper()->primaryArrayToCriteria($this->getPrimary());
        }
        $r = $this->getCrudHelper()->readOneBy($criteria);
        if ($r === null) {
            $this->existsInDatabase = false;
            $this->isSynced = false;
            return false;
        }
        $this->fromPdoArray($r->toPdoArray(), true);
        $this->isSynced = true;
        $this->existsInDatabase = true;
        return true;
    }

    /**
     * Drop the underlying SQL table for the record using the configured driver.
     *
     * @return bool True if the DDL executed successfully.
     */
    public function dropTable(): bool
    {
        return $this->getSchemaHelper()->dropTable();
    }
}
