<?php

namespace Thor\Database\PdoTable;

use Exception;
use Thor\Common\Guid;
use Thor\Database\PdoTable\{PdoRow\Attributes\Index, PdoRow\Attributes\Column, PdoRow\TableType\StringType};

/**
 * Adds a "public_id" column to a PdoRowInterface.
 *
 * @package Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license MIT
 */
#[Column('public_id', new StringType(), false)]
#[Index(['public_id'], true)]
trait HasPublicIdTrait
{

    protected ?string $public_id = null;

    /**
     * Gets the public_id of this class.
     *
     * @throws Exception
     */
    final public function getPublicId(): ?string
    {
        return $this->public_id;
    }

    /**
     * Generates a new GUID formatted public_id..
     *
     * @throws Exception
     */
    public function generatePublicId(): void
    {
        $this->public_id = Guid::hexString();
    }

}
