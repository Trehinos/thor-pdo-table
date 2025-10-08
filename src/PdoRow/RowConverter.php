<?php

namespace Thor\Database\PdoTable\PdoRow;

use Thor\Database\PdoTable\CrudHelper;

/**
 * Utility to convert between RowInterface instances and array/JSON representations.
 *
 * Supports conversions:
 * - RowInterface <-> array (PDO-friendly associative arrays)
 * - RowInterface <-> JSON string
 *
 * @package          Thor/Database/PdoTable
 *
 * @template T of RowInterface
 * @since           2020-10
 * @version         1.0
 * @author          Trehinos
 * @copyright       Author
 * @license         MIT
 */
final class RowConverter
{

    /**
     * Create a converter wrapping a RowInterface instance.
     *
     * @param RowInterface $pdoRow Row instance to wrap for conversions.
     */
    public function __construct(
        private RowInterface $pdoRow
    ) {
    }

    /**
     * Create a converter from a JSON representation.
     *
     * @param class-string<T>          $className             Fully-qualified row class name to instantiate.
     * @param string                   $json                  JSON string representing an associative array of column => value.
     * @param mixed                    ...$constructorArguments Additional constructor arguments passed to the row.
     *
     * @return self<T> Converter instance wrapping the created row.
     */
    public static function fromJson(string $className, string $json, mixed ...$constructorArguments): self
    {
        return new self(CrudHelper::instantiateFromRow($className, json_decode($json), false, ...$constructorArguments));
    }

    /**
     * Create a converter from an associative array.
     *
     * @param class-string<T>          $className             Fully-qualified row class name to instantiate.
     * @param array<string, mixed>     $data                  Associative array of column => value.
     * @param mixed                    ...$constructorArguments Additional constructor arguments passed to the row.
     *
     * @return self<T> Converter instance wrapping the created row.
     */
    public static function fromArray(string $className, array $data, mixed ...$constructorArguments): self
    {
        return new self(CrudHelper::instantiateFromRow($className, $data, false, ...$constructorArguments));
    }

    /**
     * Gets the instantiated row instance.
     *
     * @return T The wrapped RowInterface instance.
     */
    public function get(): RowInterface
    {
        return $this->pdoRow;
    }

    /**
     * Converts the current row to a JSON string.
     *
     * @return string JSON-encoded representation of toArray().
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    /**
     * Converts the current row to an associative array of column => SQL-typed value.
     *
     * @return array<string, mixed> Array compatible with PDOStatement::fetch() output.
     */
    public function toArray(): array
    {
        return $this->pdoRow->toPdoArray();
    }

}
