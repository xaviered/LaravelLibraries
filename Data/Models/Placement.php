<?php

namespace ixavier\LaravelLibraries\Data\Models;

/**
 * Class MetaDefinition holds meta definition for a given model
 */
class Placement extends Model
{
    /** @var string Table name */
    protected $table = 'placements';

    /**
     * Related models
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function models()
    {
        return $this->belongsTo(
            Model::class,
            'model_id'
        );
    }
}
