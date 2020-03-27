<?php

namespace ixavier\LaravelLibraries\Data\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class MetaDefinition holds meta definition for a given model
 *
 * @property int $id ID of this meta definition
 * @property string $deleted_at Date when this meta was deleted
 * @property string $title Title of meta
 * @property string $name Slug for this meta (must be unique across model types)
 * @property string $type Data type
 * @property string $description Description of this meta
 * @property int $model_type Model type this meta definition belongs to
 *
 */
class MetaDefinition extends DataEntry
{
    use SoftDeletes;

    /** @var string Table name */
    protected $table = 'meta_definitions';

    /** @var array The attributes that are mass assignable. */
    protected $fillable = [
        'title',
        'name',
        'type',
        'description',
        'model_type',
    ];

//    /**
//     * All related models to
//     * @return Relations\BelongsToMany
//     */
//    public function models(): Relations\BelongsToMany
//    {
//        return $this->belongsToMany(
//            Model::class,
//            (new MetaValue())->getTable(),
//            'meta_definition_id',
//            'model_id'
//        )
//            // @todo: May want to add in who columns
//            ->as('entry')
//            ->using(MetaValue::class)
//            ->withTimestamps()
//            ->withPivot([
//                'value'
//            ]);
//    }
}
