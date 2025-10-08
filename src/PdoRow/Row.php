<?php

namespace Thor\Database\PdoTable\PdoRow;

/**
 * Convenience base implementation of RowInterface using PdoRowTrait.
 *
 * Extend this class and add Column/Index/Table attributes to describe
 * your table mapping.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
abstract class Row implements RowInterface
{

    use PdoRowTrait {
        PdoRowTrait::__construct as private traitConstructor;
    }

    /**
     * Construct a Row with initial primary key values.
     *
     * @param array<string, scalar|null> $primaries Map of primary key column => value.
     */
    public function __construct(array $primaries = [])
    {
        $this->traitConstructor($primaries);
    }

}
