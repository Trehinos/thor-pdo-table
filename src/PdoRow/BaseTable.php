<?php

namespace Thor\Database\PdoTable\PdoRow;

use Thor\Database\PdoTable\HasId;
use Thor\Database\PdoTable\HasIdTrait;

/**
 * Default implementor of PdoRowInterface.
 *
 * @package Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license MIT
 */
abstract class BaseTable implements RowInterface, HasId
{

    use PdoRowTrait {
        PdoRowTrait::__construct as private traitConstructor;
    }
    use HasIdTrait;

    /**
     * @param int|null $id
     */
    public function __construct(?int $id = null)
    {
        $this->traitConstructor(['id' => $id]);
    }

}
