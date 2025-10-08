<?php

namespace Thor\Database\PdoTable;

use Exception;
use Thor\Database\PdoTable\{PdoRow\Attributes\Index, PdoRow\Attributes\Column, PdoRow\TableType\IntegerType};

/**
 * Adds an "id" column to a PdoRowInterface.
 *
 * @package Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license MIT
 */
#[Column('id', new IntegerType(), false)]
#[Index(['id'], true)]
trait HasIdTrait
{

    /**
     * Internal numeric identifier (auto-incremented primary key).
     */
    protected ?int $id = null;

    /**
     * Gets the internal numeric identifier of this row.
     *
     * @throws Exception
     */
    final public function getId(): ?int
    {
        return $this->id;
    }

}
