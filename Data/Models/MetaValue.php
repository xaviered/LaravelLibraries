<?php

namespace ixavier\LaravelLibraries\Data\Models;

use Illuminate\Database\Eloquent\Relations;

/**
 * Class MetaValue holds a value for a given model meta
 *
 * @property mixed $value Casted value
 * @property string $raw_value Raw DB value (dynamic var)
 * @property int $value_id If definition references an ID in the db, this is the indexed ID for faster reference
 * @property int $model_id Model ID this value belongs to
 * @property int $meta_definition_id MetaDefinition ID this value belongs to
 * @property MetaDefinition $metaDefinition Corresponding meta definition object
 *
 */
class MetaValue extends DataEntry
{
    /** @var string Table name */
    protected $table = 'meta_values';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'value' => 'array',
    ];

    /** @var array The attributes that are mass assignable. */
    protected $fillable = [
        'value',
        'model_id',
        'meta_definition_id',
    ];

    /**
     * @return MetaDefinition|Relations\BelongsTo
     */
    public function metaDefinition(): MetaDefinition
    {
        return $this->belongsTo(MetaDefinition::class, 'meta_definition_id')->first();
    }
}
