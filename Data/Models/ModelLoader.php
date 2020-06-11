<?php

namespace ixavier\LaravelLibraries\Data\Models;


class ModelLoader
{
    /**
     * Gets a new instance of a model from its given type.
     * Note: this will create new tables in the database to hold this type of models.
     *
     * @param string $type Type of model
     * @return Model
     */
    public function getModelType(string $type): ?Model
    {
        return new Model();
    }
}
