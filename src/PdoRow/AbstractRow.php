<?php

namespace Thor\Database\PdoTable\PdoRow;

use Thor\Database\PdoTable\HasPublicId;
use Thor\Database\PdoTable\HasPublicIdTrait;

/**
 * Extension of Row that also implements HasPublicId via HasPublicIdTrait.
 *
 * Use this as a base when your table rows expose a public identifier distinct
 * from their primary key.
 *
 * @see              Row
 * @see              HasPublicIdTrait
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
abstract class AbstractRow extends Row implements HasPublicId
{

    use HasPublicIdTrait;

    /**
     * Construct an AbstractRow, setting the optional public identifier and initial primary keys.
     *
     * @param string|null                       $public_id Public identifier to expose (distinct from primary key).
     * @param array<string, scalar|null>        $primaries Map of primary key column => value.
     */
    public function __construct(?string $public_id = null, array $primaries = [])
    {
        parent::__construct($primaries);
        $this->public_id = $public_id;
    }

}
