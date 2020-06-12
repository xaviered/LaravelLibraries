<?php

namespace ixavier\LaravelLibraries\Data\Models;

use Illuminate\Database\Eloquent;
use Illuminate\Database\Query;
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
    use Eloquent\SoftDeletes;

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

    /** @var array Basic attributes for a model */
    public const ALL_MODEL_ATTRIBUTES = [
        'id' => 'id',
        'title' => 'title',
        'type' => 'type',
        'href' => 'href',
        'alias_id' => 'alias_id',
        'content' => 'content',
        'created_at' => 'created_at',
        'created_by' => 'created_by',
        'updated_at' => 'updated_at',
        'updated_by' => 'updated_by',
        'deleted_at' => 'deleted_at',
    ];

    /** @var array Required attributes to create a new model */
    public const REQUIRED_MODEL_ATTRIBUTES = [
        'title' => 'title',
        'type' => 'type',
    ];

    /** @var string Table name */
    protected $table = 'models';

    /** @var array The attributes that are mass assignable. */
    protected $fillable = [
        'title',
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

        $this->validateAttributes($values);

        $exists = static::exists($values);
        if ($exists) {
            throw new \LogicException("Model already exists");
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
     * Validates all required attributes
     * @param array $attributes
     */
    public function validateAttributes(array $attributes)
    {
        if (empty($attributes['type']) || !is_string($attributes['type'])) {
            throw new \InvalidArgumentException("Must provide a string value for model type");
        }

        $required = static::REQUIRED_MODEL_ATTRIBUTES;
        // no need to add more requirements for alias
        // @todo: check if alias id exists
        if (!isset($attributes['alias_id']) || $attributes['alias_id'] <= 0) {
            $meta_required = MetaDefinition::getRequiredMeta($attributes['type'])->pluck('name')->toArray();
            $meta_required = array_combine($meta_required, $meta_required);
            $required = array_merge(
                array_combine($meta_required, $meta_required),
                $required
            );
        }

        $diff = array_diff_key($required, $attributes);
        if ($diff) {
            throw new \InvalidArgumentException("Must provide a value for these model attributes: " . join(', ', $diff));
        }
    }

    /**
     * Hits the database to see if the query exists based on the unique properties
     * @param array $values Query to search
     * @param string $model_type Use this model type if 'type' is not defined in the $values array
     * @param string $title Use this model title if 'title' is not defined in the $values array
     * @return bool True if it exists, false otherwise.
     */
    public static function exists(array $values, string $model_type = '', string $title = ''): bool
    {
        if (empty($model_type)) {
            if (empty($values['type']) || !is_string($values['type'])) {
                throw new \InvalidArgumentException("Must provide a string value for model type");
            }
            $model_type = $values['type'];
        }

        if (empty($title)) {
            if (empty($values['title']) || !is_string($values['title'])) {
                throw new \InvalidArgumentException("Must provide a string value for model title");
            }
            $title = $values['title'];
        }

        $query = [];
        $unique_metas = MetaDefinition::getRequiredMeta($model_type)->pluck('name')->toArray();
        if (count($unique_metas)) {
            foreach ($unique_metas as $meta) {
                if (isset($values[$meta])) {
                    $query[$meta] = $values[$meta];
                }
            }

            if ($query) {
                $query['type'] = $model_type;
                $query['title'] = $title;
            }
        }

        /** @var Eloquent\Builder $q */
        list($q, $model_query, $meta_query) = static::prepareSearchQuery($query);
        $count = $q->count();

        $expected_count = 1;

        if ($meta_query) {
            $expected_count += count($meta_query);
        }

        return $count === $expected_count;
    }

    public static function search(array $query): Eloquent\Collection
    {
        /** @var Eloquent\Builder $q */
        list($q, $model_query, $meta_query) = static::prepareSearchQuery($query);
        return $q->get();
    }

    /**
     * Prepares search query based on query params
     * @param array $query Query params
     * @return array [Eloquent\Builder, array, array]
     */
    public static function prepareSearchQuery(array $query): array
    {
        $m_table = static::getTableName();
        list($model_query, $meta_query) = static::split_attributes($query, $m_table);

        // first query to search only on model
        $q = static::query()
            ->select("{$m_table}.*")
            ->where($model_query);

        if (count($meta_query)) {
            $md_table = MetaDefinition::getTableName();
            $mv_table = MetaValue::getTableName();

            $metaq = static::query()
                ->select("{$m_table}.*")
                ->where($model_query)
                ->join($md_table, function (Query\JoinClause $join) use ($m_table, $md_table, $meta_query) {
                    $join->whereRaw("{$m_table}.type = {$md_table}.model_type")
                        ->whereIn("{$md_table}.name", array_keys($meta_query));
                })
                ->join($mv_table, function (Query\JoinClause $join) use ($m_table, $md_table, $mv_table, $meta_query) {
                    $join
                        ->whereRaw("{$m_table}.id = {$mv_table}.model_id")
                        ->whereRaw("{$md_table}.id = {$mv_table}.meta_definition_id")
                        ->whereIn("{$mv_table}.value", array_values($meta_query));
                });

            $q = $q->union($metaq, true);
        }

        return [$q, $model_query, $meta_query];
    }

    protected static function split_attributes(array $attributes, string $table_name = ''): array
    {
        $table_name = $table_name ?: static::getTableName();
        $model_query = [];
        $meta_query = [];
        foreach ($attributes as $name => $value) {
            if (isset(static::ALL_MODEL_ATTRIBUTES[$name])) {
                $model_query["{$table_name}.{$name}"] = $value;
            } else {
                $meta_query[$name] = $value;
            }
        }

        return [$model_query, $meta_query];
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
