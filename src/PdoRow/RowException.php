<?php

namespace Thor\Database\PdoTable\PdoRow;

use Throwable;
use RuntimeException;
use JetBrains\PhpStorm\Pure;

/**
 * Exception type for runtime errors in the PdoTable Row subsystem.
 *
 * @package          Thor/Database/PdoTable
 *
 * @since            2020-10
 * @version          1.0
 * @author           Trehinos
 * @license          MIT
 */
class RowException extends RuntimeException
{

    /**
     * Construct a RowException.
     *
     * @param string          $message  Error message.
     * @param int             $code     Error code.
     * @param Throwable|null  $previous Previous exception for chaining.
     */
    #[Pure]
    public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
