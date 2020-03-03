<?php

namespace ixavier\LaravelLibraries\Data\Models;

use \Illuminate\Database\Eloquent\Relations;

/**
 * Class MetaDefinition holds meta definition for a given model
 *
 * @property int $id ID of this entry
 * @property int $model_id ID of model
 * @property int $parent_id ID of parent of model
 * @property array $children All children under the model
 */
class Placement extends DataEntry
{
    /** @var string Table name */
    protected $table = 'placements';

    /** @var array The attributes that are mass assignable. */
    protected $fillable = [
        'model_id',
        'parent_id',
        'children',
    ];

    /**
     * Related models
     * @return Relations\BelongsTo
     */
    public function models(): Relations\BelongsTo
    {
        return $this->belongsTo(
            Model::class,
            'model_id'
        );
    }

    /**
     * @return Relations\BelongsTo|Model|null Null if placement has no parent
     */
    public function parent(): ?Model
    {
        if (!empty($this->parent_id)) {
            return $this->belongsTo(Model::class, 'parent_id');
        }
        return null;
    }

    /**
     * Mutator to get JSON into array
     * @param string $value JSON value
     * @return array
     */
    public function getChildrenAttribute(string $value): array
    {
        return json_decode($value, JSON_OBJECT_AS_ARRAY) ?? [];
    }

    /**
     * Mutator to get array to JSON
     * @param array $value Array value
     * @return string
     */
    public function setChildrenAttribute(array $value): string
    {
        return json_encode($value);
    }
}
