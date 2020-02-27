<?php

namespace ixavier\LaravelLibraries\Data\Models;

use ixavier\LaravelLibraries\Data\Models\Traits\HasMeta;
use ixavier\LaravelLibraries\Data\Models\Traits\HasPlacements;
use ixavier\LaravelLibraries\Http\Resources\BaseResource;
use ixavier\LaravelLibraries\Http\Resources\BaseResourceCollection;

class Model extends BaseModel
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

    /**
     * Resource to load model
     * @return BaseResource|null
     */
    public function getResource(): BaseResource
    {
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
