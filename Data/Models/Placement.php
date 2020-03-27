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

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'children' => 'array',
    ];

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
}
