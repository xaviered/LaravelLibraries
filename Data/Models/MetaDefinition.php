<?php

namespace ixavier\LaravelLibraries\Data\Models;

use \Illuminate\Database\Eloquent\Relations;
use ixavier\LaravelLibraries\Data\Models\Relationships\MetaValue;

/**
 * Class MetaDefinition holds meta definition for a given model
 */
class MetaDefinition extends Model
{
    /** @var string Table name */
    protected $table = 'meta_definition';

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
