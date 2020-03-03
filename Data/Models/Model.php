<?php

namespace ixavier\LaravelLibraries\Data\Models;

use ixavier\LaravelLibraries\Data\Models\Traits\HasMeta;
use ixavier\LaravelLibraries\Data\Models\Traits\HasPlacements;
use ixavier\LaravelLibraries\Http\Resources\BaseResource;
use ixavier\LaravelLibraries\Http\Resources\BaseResourceCollection;

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
    protected $defaultAttributes = [
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
     * @param int|null $parentId Parent ID to use
     *  If null, will use $this as parent
     *  If zero, will not attach a parent to this model (is a root model)
     * @param bool $ignoreNonExistingMeta If true, will not throw exception when a meta is not defined, will just ignore.
     * @return Model New created model
     * @throws \Exception If cannot save
     */
    public function create(array $values, ?int $parentId = null, bool $ignoreNonExistingMeta = true): Model
    {
        /** @var Model $parent */
        $parent = null;
        if (is_null($parentId)) {
            $parentId = $this->id;
        } else if ($parentId > 0) {
            $parent = Model::query()->findOrFail($parentId);
        }

        $model = new Model;
        $model->setAttributes($values);
        if ($model->save()) {
            $model->placement()->create([
                'model_id' => $model->id,
                'parent_id' => $parentId,
            ]);

            if ($parent) {
                $parent->placement->children[] = $this->id;
                // dynamically inject object so we don't have to reload from db
                $parent->children()->add($this);
            }
        } else {
            throw new \Exception("Could not save model");
        }

        return $model;
    }

    /**
     * Sets attributes, respecting mutator methods
     * @param array $values All values. If any value is not in $this->defaultAttributes
     *  they will be saved as meta values. Meta values need to be defined before being set.
     *  {@see setMetaDefinition} to add a meta definition.
     * @param bool $ignoreNonExistingMeta If true, will not throw exception when a meta is not defined, will just ignore.
     * @return void
     * @throws \Exception Validation exception
     */
    public function setAttributes(array $values, bool $ignoreNonExistingMeta = true): void
    {
        foreach ($this->defaultAttributes as $defaultAttribute) {
            if (isset($values[$defaultAttribute])) {
                $this->setAttribute($defaultAttribute, $values[$defaultAttribute]);
                unset($values[$defaultAttribute]);
            }
        }
        /** @var \Exception $error */
        $error = null;
        // everything else goes into meta
        foreach ($values as $key => $value) {
            try {
                $this->setMetaValue($key, $value);
            } catch (\InvalidArgumentException $e) {
                if (!$ignoreNonExistingMeta) {
                    $error = new \InvalidArgumentException($e->getMessage(), $e->getCode(), $error);
                }
            } catch (\TypeError $e) {
                $error = new \TypeError($e->getMessage(), $e->getCode(), $error);
            }
        }
        if ($error) {
            throw $error;
        }
    }

    /**
     * Resource to load model
     * @return BaseResource|null
     */
    public function getResource(): BaseResource
    {
        $this->touches;
        $class_name = static::class;
        $dir_class_name = dirname($class_name);
        $base_class_name = basename($class_name);
        $class = $dir_class_name . '\\Http\\Resource\\' . $base_class_name;
        if (class_exists($class)) {
            return new $class($this);
        }

        return new BaseResource($this);
    }

    // @todo: Move this to the collection loader
    public function getResourceCollection(): ?BaseResourceCollection
    {
        $class_name = static::class;
        $dir_class_name = dirname($class_name);
        $base_class_name = basename($class_name);
        $class = $dir_class_name . '\\Http\\Resource\\' . $base_class_name . 'Collection';
        if (class_exists($class)) {
            return new $class([$this]);
        }

        return null;
    }
}
