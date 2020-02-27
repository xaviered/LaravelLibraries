<?php

namespace ixavier\LaravelLibraries\Data\Models;

use \Illuminate\Database\Eloquent\Relations;

/**
 * Class MetaDefinition holds meta definition for a given model
 */
class Placement extends Model
{
    /** @var string Table name */
    protected $table = 'placements';

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
     * @return Relations\BelongsTo|Model
     */
    public function parent(): Relations\BelongsTo
    {
        return $this->belongsTo(Model::class, 'parent_id');
    }
}
