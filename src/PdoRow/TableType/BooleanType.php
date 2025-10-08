<?php

namespace Thor\Database\PdoTable\PdoRow\TableType;

/**
 * TableType representing boolean values stored as small integers or custom
 * truthy/falsey tokens at the SQL level.
 *
 * By default, TRUE is stored as '1' and FALSE as '0' using an INTEGER(1) column.
 *
 * @package          Thor/Database/PdoTable
 * @copyright (2021) Sébastien Geldreich
 * @license          MIT
 */
class BooleanType extends BaseType
{

    /**
     * Configure the SQL type and tokens used to represent boolean values.
     *
     * @param string $sqlType  SQL type/definition to use (e.g. 'INTEGER(1)').
     * @param string $sqlTrue  Token used to store TRUE in SQL.
     * @param string $sqlFalse Token used to store FALSE in SQL.
     */
    public function __construct(
        string $sqlType = 'INTEGER(1)',
        private readonly string $sqlTrue = '1',
        private readonly string $sqlFalse = '0'
    ) {
        parent::__construct($sqlType, 'bool');
    }

    /**
     * Convert the SQL token to a PHP boolean.
     *
     * @param mixed $sqlValue Raw SQL value.
     *
     * @return bool TRUE when equal to the configured true token; FALSE otherwise.
     */
    public function toPhpValue(mixed $sqlValue): bool
    {
        return $sqlValue === $this->sqlTrue;
    }

    /**
     * Convert a PHP boolean to its SQL token.
     *
     * @param mixed $phpValue PHP boolean.
     *
     * @return string The configured token for TRUE or FALSE.
     */
    public function toSqlValue(mixed $phpValue): string
    {
        return $phpValue ? $this->sqlTrue : $this->sqlFalse;
    }

}
