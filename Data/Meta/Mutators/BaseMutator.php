<?php

namespace ixavier\LaravelLibraries\Data\Meta\Mutators;

/**
 * Base class for dealing with data type strings
 */
class BaseMutator
{
    /** @var mixed|string|int Actual value */
    protected $value;

    /**
     * DataTypeInterface constructor.
     * @param mixed $value Value for this data
     * @param int $encoding_options Encoding options for JSON values
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed Serialized value for data store
     */
    public function serialize()
    {
        return (string) $this->value;
    }

    /**
     * @return mixed Deserialized value for app use
     */
    public function deserialize()
    {
        return $this->value;
    }

    /**
     * @return bool If value, is an actual ID reference to the DB
     */
    public static function isValueReference(): bool
    {
        return false;
    }
}
