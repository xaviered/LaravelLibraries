<?php

namespace ixavier\LaravelLibraries\Data\Models\Traits;

use Illuminate\Database\Query;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations;
use Illuminate\Support\Facades\Log;
use ixavier\LaravelLibraries\Data\Models\Model;
use ixavier\LaravelLibraries\Data\Models\Placement;

/**
 * Trait HasPlacements contains all placement functionality
 *
 * @property Model $original If this is an alias, this is the original model
 * @property Model $parent Parent of this model
 * @property Placement $placement Placement obj
 */
trait HasPlacements
{
    /**
     * Related placements
     * @return Relations\HasOne|Placement
     */
    public function placement()
    {
        return $this->hasOne(
            Placement::class,
            'model_id',
            'id'
        );
    }

    /**
     * Helper method to get original model if this is an alias (through alias_id property)
     * @return Model|null Null if this is not an alias
     */
    public function getOriginalModel(): ?Model
    {
        if ($this->isAlias()) {
            return $this->original()->first();
        }

        return null;
    }

    /**
     * @return Relations\BelongsTo|Model
     */
    public function original(): ?Model
    {
        return $this->belongsTo(Model::class, 'alias_id');
    }

    /**
     * @return Relations\HasMany
     */
    public function aliases(): Relations\HasMany
    {
        return $this->hasMany(Model::class, 'alias_id', 'id');
    }

    /**
     * @return bool True if model is an alias
     */
    public function isAlias(): bool
    {
        return !empty($this->alias_id);
    }


    /**
     * @return Model|null Parent model, null if model is a root
     */
    public function parent()
    {
        /** @var Placement $placement */
        return $this->placement->parent()->first();
    }

    /**
     * Gets a collection of Model objects that are children of this model.
     *
     * @param bool $loadChildrenMode Mode to load children.
     *  Model::CHILDREN_FROM_CURRENT_MODEL: Load only children from this model.
     *  Model::CHILDREN_FROM_ORIGINAL_MODEL: Load children from original model; this model needs to be an alias.
     *  Model::CHILDREN_FROM_CURRENT_AND_ORIGINAL_MODEL: Load all children; Merge children from alias and original.
     *
     * @return Collection
     * @throws \InvalidArgumentException When $loadChildrenMode is CHILDREN_FROM_ORIGINAL_MODEL or is not any of the valid modes.
     */
    public function children(bool $loadChildrenMode = Model::CHILDREN_FROM_CURRENT_MODEL): Collection
    {
        /** @var Model $this */
        switch ($loadChildrenMode) {
            case Model::CHILDREN_FROM_CURRENT_MODEL:
                return $this->loadChildren($this);

            case Model::CHILDREN_FROM_ORIGINAL_MODEL:
                // @todo: Maybe not throw exception
                if (!$this->isAlias()) {
                    throw new \InvalidArgumentException("Must be an alias model to load children from original model");
                }
                if ($original = $this->getOriginalModel()) {
                    return $original->children(Model::CHILDREN_FROM_ORIGINAL_MODEL);
                }
                // @todo: add log: Trying to load an original model that is deleted
                return new Collection();

            case Model::CHILDREN_FROM_CURRENT_AND_ORIGINAL_MODEL:
                $children = $this->loadChildren($this);
                if ($original = $this->getOriginalModel()) {
                    return $original->children(Model::CHILDREN_FROM_ORIGINAL_MODEL);
                } else {
                    // @todo: add log: Trying to load an original model that is deleted
                }

                return $children;

            default:
                throw new \InvalidArgumentException("Invalid mode for \$loadChildrenMode argument. See method docs.");
        }
    }

    /**
     * Helper method to load children under a given model.
     * @param Model $model Parent model for children
     * @return Collection
     */
    private function loadChildren(Model $model): Collection
    {
        $modelId = $model->id;
        $mtable = $this->getTable();
        $ptable = (new Placement())->getTable();
        $q = Model::query()
            ->select(["{$mtable}.*", "{$ptable}.children as children"])
            ->join($ptable, function (Query\JoinClause $join) use ($ptable, $mtable, $modelId) {
                $join->where("{$ptable}.model_id", '=', $modelId)
                    ->whereRaw("JSON_CONTAINS(`{$ptable}`.`children`, CAST(`{$mtable}`.`id` as JSON))");
            })
            ->orderBy("children");

        return $q->get();
    }
}
