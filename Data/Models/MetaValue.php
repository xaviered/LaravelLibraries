<?php

namespace ixavier\LaravelLibraries\Data\Models;

use Illuminate\Database\Eloquent\Relations;

/**
 * Class MetaValue holds a value for a given model meta
 *
 * @property mixed $value Casted value
 * @property string $raw_value Raw DB value (dynamic var)
 * @property int $model_id Model ID this value belongs to
 * @property int $meta_definition_id MetaDefinition ID this value belongs to
 * @property MetaDefinition $metaDefinition Corresponding meta definition object
 *
 */
class MetaValue extends DataEntry
{
    /** @var string Table name */
    protected $table = 'meta_values';

    /** @var array The attributes that are mass assignable. */
    protected $fillable = [
        'value',
        'model_id',
        'meta_definition_id',
    ];

    /**
     * @return string Raw value from db
     */
    public function getRawValue(): string
    {
        return $this->getAttribute('value');
    }

    /**
     * @param string $value DB raw value
     */
    public function setRawValue(string $value): void
    {
        $this->setAttribute('value', $value);
    }

    /**
     * Proper value type defined on its MetaDefinition
     * @return mixed
     */
    public function getValue()
    {
        return $this->getRawValue();
    }

    /**
     * Sets proper value type defined on its MetaDefinition
     * @param mixed|MetaValue $value Value to set. If passed a MetaValue, it needs to be of same MetaDefinition type
     * @throws \TypeError When MetaDefinition type of MetaValue value passed is not of the same type as this one
     */
    public function setValue($value): void
    {
        if ($value instanceof self) {
            if ($value->metaDefinition()->type !== $this->metaDefinition()->type) {
                throw new \InvalidArgumentException(
                    "MetaValue argument must be of the same data type defined by its MetaDefinition"
                );
            }
            $value = $value->getValue();
        }
        $this->setRawValue($value);
    }

    /**
     * @return MetaDefinition|Relations\BelongsTo
     */
    public function metaDefinition(): MetaDefinition
    {
        return $this->belongsTo(MetaDefinition::class, 'meta_definition_id')->first();
    }

    /**
     * Magic method catcher for value attribute
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value): void
    {
        if ($key === 'value') {
            $this->setValue($value);
            return;
        } elseif ($key === 'raw_value') {
            $this->setRawValue($value);
            return;
        }
        parent::__set($key, $value);
    }

    /**
     * Magic method catcher for value attribute
     * @param string $key
     * @return mixed
     */
    public function __get($key)
    {
        if ($key === 'value') {
            return $this->getValue();
        } elseif ($key === 'raw_value') {
            return $this->getRawValue();
        }
        return parent::__get($key);
    }
}
