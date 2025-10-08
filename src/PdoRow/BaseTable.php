<?php

namespace Thor\Database\PdoTable\PdoRow;

use Thor\Database\PdoTable\HasId;
use Thor\Database\PdoTable\HasIdTrait;

/**
 * Base class combining RowInterface behavior (via PdoRowTrait) with HasId semantics.
 *
 * Extend this when your table rows have a single integer primary key named "id".
 * The constructor initializes the RowInterface primary map accordingly.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
abstract class BaseTable implements RowInterface, HasId
{

    use PdoRowTrait {
        PdoRowTrait::__construct as private traitConstructor;
    }
    use HasIdTrait;

    /**
     * Construct a BaseTable setting the optional integer primary key.
     *
     * The provided $id is stored into the RowInterface primary map under the 'id' key.
     *
     * @param int|null $id Primary key value for the row (or null for unsaved/new rows).
     */
    public function __construct(?int $id = null)
    {
        $this->traitConstructor(['id' => $id]);
    }

}
