<?php

namespace ixavier\LaravelLibraries\Data\Models\Traits;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Relations;
use ixavier\LaravelLibraries\Data\Models\MetaDefinition;
use ixavier\LaravelLibraries\Data\Models\Model;
use ixavier\LaravelLibraries\Data\Models\MetaValue;

/**
 * Trait contains functionality to handle meta values for models
 */
trait HasMeta
{
    /** @var Collection Of meta definitions as [$metaName => MetaDefinition] */
    protected $metaDefinitionObjects;

    /** @var Collection Of meta values as [$metaName => MetaValue] */
    protected $metaValueObjects;


    /**
     * @throws \LogicException When model is not saved and doesn't have type attribute set
     */
    private function validateIsSaved(): void
    {
        if (!$this->isSaved()) {
            throw new \LogicException("Model needs to be saved on db and have a type attribute defined.");
        }
    }

    /**
     * All meta definitions for this model
     * @return Relations\HasMany
     */
    protected function metaDefinitions(): Relations\HasMany
    {
        /** @var Model $this Model */
        return $this->hasMany(MetaDefinition::class, 'model_type', 'type');
    }

    /**
     * Collection of all meta definitions as [$metaName => MetaDefinition]
     *
     * @param bool $force If true, will reload data
     * @return Collection
     */
    public function getMetaDefinitionObjects(bool $force = false): Collection
    {
        if (!isset($this->metaDefinitionObjects) || $force) {
            $this->metaDefinitionObjects = $this->metaDefinitions()->get()->keyBy('name');
        }
        return $this->metaDefinitionObjects;
    }

    /**
     * Gets a MetaDefinition object for the given meta name
     *
     * @param string $metaName Meta name to load
     * @return MetaDefinition
     */
    public function getMetaDefinition(string $metaName): ?MetaDefinition
    {
        return $this->getMetaDefinitionObjects()->get($metaName);
    }

    /**
     * Creates/updates a MetaDefinition object for the given meta name
     *
     * @param MetaDefinition $metaDefinition Value as MetaDefinition
     * @return void
     */
    public function setMetaDefinition(MetaDefinition $metaDefinition): void
    {
        $this->validateIsSaved();

        $metaName = $metaDefinition->name;

        $attributes = $metaDefinition->attributesToArray();
        unset($attributes['id']);

        /** @var MetaDefinition $md */
        $md = $this->getMetaDefinition($metaName) ?? new MetaDefinition();
        $md->setRawAttributes($attributes);

        $md->model_type = $this->type;
        $md->save();
        $this->getMetaDefinitionObjects()->put($metaName, $md);
    }

    /**
     * @param string $metaName Meta name to check
     * @return bool
     */
    public function hasMetaDefinition(string $metaName): bool
    {
        return $this->getMetaDefinition($metaName) ? true : false;
    }

    /**
     * All meta values for this model
     * @return Relations\BelongsToMany
     */
    protected function metaValues(): Relations\BelongsToMany
    {
        /** @var Model $this Model */
        return $this->belongsToMany(
            MetaDefinition::class,
            (new MetaValue())->getTable(),
            'model_id',
            'meta_definition_id'
        )
            // @todo: May want to add in who columns
            ->as('entry')
            ->using(MetaValue::class)
            ->withPivot([
                'value'
            ]);
    }

    /**
     * Collection of only defined meta values as [$metaName => MetaDefinition]
     * Use $this->getMetaValueObjects()->get('name')->entry to get actual MetaValue object
     *
     * @param bool $force If true, will reload data
     * @return Collection
     */
    public function getMetaValueObjects(bool $force = false): Collection
    {
        if (!isset($this->metaValueObjects) || $force) {
            $this->metaValueObjects = $this->metaValues()->get()->keyBy('name');
        }
        return $this->metaValueObjects;
    }

    /**
     * Gets a MetaValue object for the given meta name
     *
     * @param string $metaName Meta name to load
     * @return MetaValue|null
     */
    public function getMetaValue(string $metaName): ?MetaValue
    {
        return $this->getMetaValueObjects()->get($metaName)->entry;
    }

    /**
     * Creates/updates a meta value
     *
     * @param string $metaName Meta name
     * @param MetaValue|mixed $value Value as MetaValue or a scalar
     * @return void
     * @throws \TypeError If $value is not a MetaValue or a scalar
     * @throws \InvalidArgumentException Meta definition not found.
     */
    public function setMetaValue(string $metaName, $value): void
    {
        $this->validateIsSaved();

        $md = $this->getMetaDefinition($metaName);
        if ($md) {
            if (!($value instanceof MetaValue) || !is_scalar($value)) {
                throw new \TypeError("Value needs to be a MetaValue or a scalar");
            }

            $mv = $this->getMetaValue($metaName) ?? new MetaValue();
            $mv->value = $value;
            $mv->model_id = $this->id;
            $mv->meta_definition_id = $md->id;
            $mv->save();

            // @todo: May need to do attach or update here
            $this->metaValues()->attach($mv->id);
            $this->metaValueObjects->put($metaName, $mv);
        } else {
            throw new \InvalidArgumentException("There is no meta definition for $metaName");
        }
    }

    /**
     * @param string $metaName Meta name to check
     * @return bool
     */
    public function hasMetaValue(string $metaName): bool
    {
        return $this->getMetaValueObjects()->get($metaName) ? true : false;
    }

    /**
     * Magic method for dynamically setting a meta value
     *
     * @param string $name Key of variable
     * @param mixed $value Value of variable
     */
    public function __set($name, $value)
    {
        if ($this->hasMetaDefinition($name)) {
            $this->setMetaValue($name, $value);
            return;
        }

        parent::__set($name, $value);
    }

    /**
     * Magic method for dynamically getting a meta value
     *
     * @param string $name Key of variable
     * @return mixed
     */
    public function __get($name)
    {
        if ($this->hasMetaDefinition($name)) {
            return $this->getMetaValue($name);
        }

        return parent::__get($name);
    }
}
