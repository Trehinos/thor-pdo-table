<?php

namespace Thor\Database\PdoTable\PdoRecord;

use Thor\Database\PdoTable\PdoRow\PdoRowTrait;

/**
 * Concrete Record implementation combining PdoRow behavior with Record features.
 *
 * This class wires PdoRowTrait and RecordTrait together and bootstraps itself
 * by reloading from the database when primaries are provided.
 */
class Record implements RecordInterface
{
    use PdoRowTrait {
        PdoRowTrait::__construct as private pdoRow;
    }
    use RecordTrait {
        RecordTrait::__construct as private pdoRecord;
    }

    /**
     * Construct a Record that is immediately reloaded from the database.
     *
     * @param RecordManager $manager Manager providing CrudHelper and SchemaHelper.
     * @param array $primaries Primary key values for this record; empty array creates an empty object.
     */
    public function __construct(RecordManager $manager, array $primaries)
    {
        $this->pdoRow($primaries);
        $this->pdoRecord($manager);
        $this->objectEmpty = empty($primaries);
        $this->reload();
    }

}
