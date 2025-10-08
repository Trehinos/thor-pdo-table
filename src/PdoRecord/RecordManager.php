<?php

namespace Thor\Database\PdoTable\PdoRecord;

use Thor\Database\PdoExtension\Requester;
use Thor\Database\PdoTable\CrudHelper;
use Thor\Database\PdoTable\Driver\DriverInterface;
use Thor\Database\PdoTable\PdoRow\RowInterface;
use Thor\Database\PdoTable\SchemaHelper;

/**
 * Aggregates the CrudHelper and SchemaHelper required by a Record.
 *
 * This lightweight service bundles access to data operations (via CrudHelper)
 * and DDL generation/execution (via SchemaHelper) so Record instances can be
 * constructed with a single dependency.
 */
final readonly class RecordManager
{

    /**
     * Create a new manager bundling CRUD and schema helpers.
     *
     * @param CrudHelper   $crud   Helper for data operations on the given row class.
     * @param SchemaHelper $schema Helper for DDL generation/execution for the given row class.
     */
    public function __construct(public CrudHelper $crud, public SchemaHelper $schema) {}

    /**
     * Factory that wires helpers and instantiates a concrete Record class.
     *
     * @template T of RowInterface
     *
     * @param DriverInterface   $driver    SQL dialect driver used by SchemaHelper.
     * @param Requester         $requester PDO requester used by CrudHelper.
     * @param class-string<T>   $class     Fully-qualified class name of the record to instantiate.
     *
     * @return T An instance of the provided class, constructed with a RecordManager.
     */
    public static function create(DriverInterface $driver, Requester $requester, string $class): object
    {
        return new $class(
            new CrudHelper($class, $requester),
            new SchemaHelper($requester, $driver, $class)
        );
    }

}
