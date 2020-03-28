<?php

namespace ixavier\LaravelLibraries\Data\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

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

    /**
     * Creates in bulk
     * @param Collection $meta_definitions A collection of MetaDefinition objects
     * @param string $model_type Bulk create these meta definitions under a particular model type
     * @param bool $override_existing_meta_definition Override existing meta definition with the ones provided
     * @return Collection All MetaDefinition objects
     */
    public function bulkCreate(Collection $meta_definitions, string $model_type, bool $override_existing_meta_definition = false)
    {
        $existing_entries = static::query()
            ->where('model_type', '=', $model_type)
            ->whereIn('name', $meta_definitions->pluck('name'))
            ->get()
            ->keyBy('name');

        $all_entries = new Collection();

        /** @var MetaDefinition $meta_definition */
        foreach ($meta_definitions as $meta_definition) {
            // don't override existing db entries
            if (!$override_existing_meta_definition && $existing_entries->offsetExists($meta_definition->name)) {
                $all_entries->put($meta_definition->name, $meta_definition);
                continue;
            }

            $attributes = $meta_definition->attributesToArray();
            unset($attributes['id']);
            $attributes['model_type'] = $model_type;

            $md = $existing_entries->get($meta_definition->name) ?? new MetaDefinition();
            $md->setRawAttributes($attributes);
            $md->save();

            $all_entries->put($md->name, $md);
        }

        return $all_entries;
    }

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
