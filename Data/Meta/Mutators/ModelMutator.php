<?php

namespace ixavier\LaravelLibraries\Data\Meta\Mutators;

use Illuminate\Database\Eloquent;
use ixavier\LaravelLibraries\Data\Models\Model;

/**
 * Class loads models from given ID value
 */
class ModelMutator extends BaseMutator
{
    /**
     * @return string Ready for data store
     */
    public function serialize()
    {
        // remember, the actual value should now be a Model instance
        if ($this->value instanceof Model && $this->value->id > 0) {
            return (string) $this->value->id;
        } else if (is_int($this->value) && $this->value > 0) {
            return (string) $this->value;
        } else if (!is_null($this->value)) {
            throw new \InvalidArgumentException("Model needs to be a positive integer or an existing Model object");
        }

        return null;
    }

    /**
     * @return Eloquent\Model|Model|null Model object if its a model ID
     */
    public function deserialize()
    {
        return $this->getDataEntryQuery()->where('id', '=', (int) $this->value)->first();
    }

    /**
     * Loads from the given model query.
     * Override this method to load from other data models
     * @return Eloquent\Builder
     */
    protected function getDataEntryQuery(): Eloquent\Builder
    {
        return Model::query();
    }

    /** @inheritDoc */
    public static function isValueReference(): bool
    {
        return true;
    }
}
