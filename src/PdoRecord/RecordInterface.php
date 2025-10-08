<?php

namespace Thor\Database\PdoTable\PdoRecord;

use Thor\Database\PdoTable\CrudHelper;
use Thor\Database\PdoExtension\Criteria;
use Thor\Database\PdoTable\PdoRow\RowInterface;
use Thor\Database\PdoTable\SchemaHelper;

/**
 * Contract for a PdoRow that is aware of its CRUD and schema helpers.
 *
 * A Record brings higher-level operations (insert, update, upsert, delete),
 * synchronization helpers, and table management (create/drop) built on top
 * of CrudHelper and SchemaHelper.
 */
interface RecordInterface extends RowInterface
{

    /**
     * Access the CRUD helper used by this record to interact with the database.
     *
     * @return CrudHelper
     */
    public function getCrudHelper(): CrudHelper;

    /**
     * Access the schema helper used to generate and execute DDL (CREATE/DROP).
     *
     * @return SchemaHelper
     */
    public function getSchemaHelper(): SchemaHelper;

    /**
     * Create the underlying table for this record.
     *
     * @return bool True if the DDL executed successfully.
     */
    public function createTable(): bool;

    /**
     * - Returns `true` if the record exists in the database and is synchronized with this object.
     * - Returns `false` if the record exists in the database but is not synchronized with this object.
     * - Returns `null` if the record does not exist in the database or if this object is empty.
     *
     * @return ?bool
     */
    public function synced(): ?bool;

    /**
     * Inserts this PdoRowInterface in the corresponding SQL Table.
     *
     * @return bool
     */
    public function insert(): bool;

    /**
     * Updates this PdoRowInterface in the corresponding SQL Table.
     *
     * @return bool
     */
    public function update(): bool;

    /**
     * Inserts or updates this PdoRowInterface in the corresponding SQL Table.
     *
     * @return bool
     */
    public function upsert(): bool;

    /**
     * Deletes this PdoRowInterface in the corresponding SQL Table.
     *
     * @return bool
     */
    public function delete(): bool;

    /**
     * Change all the data of this object with new data from the database.
     *
     * @param Criteria|null $criteria
     *
     * @return bool
     */
    public function reload(?Criteria $criteria = null): bool;

    /**
     * Drop the underlying table for this record.
     *
     * @return bool True if the DDL executed successfully.
     */
    public function dropTable(): bool;

}
