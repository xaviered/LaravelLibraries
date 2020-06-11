<?php

namespace ixavier\LaravelLibraries\Data\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use ixavier\LaravelLibraries\Data\Models\Traits\HasMeta;
use ixavier\LaravelLibraries\Data\Models\Traits\HasPlacements;
use ixavier\LaravelLibraries\Http\Resources\ModelResource;
use ixavier\LaravelLibraries\Http\Resources\ModelResourceCollection;

/**
 * Class Model
 *
 * @property int $id ID of this model
 * @property string $created_at Date when this model was created
 * @property int $created_by ID of who created this model
 * @property string $updated_at Date when this model was last updated
 * @property int $updated_by ID of who last updated this model
 * @property string $deleted_at Date when this model was deleted
 * @property string $title Title of model
 * @property string $type Type of model
 * @property string $href Hyperlink related to this model
 * @property int $alias_id ID of the original model if this model is an alias
 * @property string $content Content of this model
 *
 */
class Model extends DataEntry
{
    use HasMeta;
    use HasPlacements;
    use SoftDeletes;

    // @todo: Add a check to allow to be null only if user is root
    /** @var string Column name for created timestamp */
    const CREATED_AT = 'created_at';

    // @todo: Add a check to allow to be null only if user is root
    /** @var string Column name for updated timestamp */
    const UPDATED_AT = 'updated_at';

    /** @var int When fetching children of a model and it is an alias, will fetch original model children too */
    public const CHILDREN_FROM_CURRENT_AND_ORIGINAL_MODEL = 1;

    /** @var int Fetch only children under the given model */
    public const CHILDREN_FROM_CURRENT_MODEL = 2;

    /** @var int Fetch only children under the original model if current model is an alias */
    public const CHILDREN_FROM_ORIGINAL_MODEL = 3;

    /** @var string Table name */
    protected $table = 'models';

    /** @var array The attributes that are mass assignable. */
    protected $fillable = [
        'title',
    ];

    /** @var array Attributes that belong straight to the base model */
    protected $default_attributes = [
        'id',
        'title',
        'type',
        'href',
        'alias_id',
        'content',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
        'deleted_at',
    ];

    /** @var array The relationships that should be touched on save. */
    protected $touches = ['metaDefinitions', 'metaValues'];

    /**
     * Helper function to create a model and dependencies
     *
     * @param array $values All values. If any value is not in $this->defaultAttributes
     *  they will be saved as meta values.
     * @param int|null $parent_id Parent ID to use
     *  If null, will use $this as parent
     *  If zero, will not attach a parent to this model (is a root model)
     * @param bool $ignore_non_existing_meta If true, will not throw exception when a meta is not defined, will just ignore.
     * @param Collection $meta_definitions A collection of MetaDefinition objects
     * @param bool $override_existing_meta_definition Override existing meta definition with the ones provided
     * @return Model New created model
     * @throws \Exception If cannot save
     */
    public function create(
        array $values,
        ?int $parent_id = null,
        bool $ignore_non_existing_meta = true,
        ?Collection $meta_definitions = null,
        bool $override_existing_meta_definition = false
    ): Model
    {
        /** @var Model $parent */
        $parent = null;
        if (is_null($parent_id)) {
            $parent_id = $this->id;
        } else if ($parent_id > 0) {
            $parent = Model::query()->findOrFail($parent_id);
        }

        $model = new Model;
        $model->setAttributes($values, true, false);
        if ($model->save()) {
            // define meta definitions
            if ($meta_definitions && $meta_definitions->count()) {
                $model->setMetaDefinitions($meta_definitions);
            }
            $attr_diff = array_diff_key($values, $model->getAttributes());
            if (count($attr_diff)) {
                $model->setMetaValues($attr_diff, $ignore_non_existing_meta);
            }
            if (!$model->save()) {
                throw new \Exception("Could not save model");
            }

            $model->placement()->create([
                'model_id' => $model->id,
                'parent_id' => $parent_id,
            ]);

            if ($parent) {
                $c = $parent->placement->getAttribute('children');
                $c[] = $model->id;
                $parent->placement->setAttribute('children', $c);
                $parent->placement->save();
                // dynamically inject object so we don't have to reload from db
                $parent->children()->add($this);
            }
        } else {
            throw new \Exception("Could not save model");
        }

        return $model;
    }

    /**
     * @return bool If attributes are saved on db
     */
    public function isSaved()
    {
        return !empty($this->id) && !empty($this->type);
    }

    /**
     * Quick view of object
     * @return string
     */
    public function toString()
    {
        $attributes = $this->getAllAttributes();
        return 'object(' . $this->type . ' Model) - ' . count($attributes) . ' attributes:' . PHP_EOL
            . print_r($attributes, 1);
    }

    /**
     * @return array Data for API response
     */
    public function toArray()
    {
        return $this->getAllAttributes();
    }
}
