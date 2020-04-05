<?php

namespace ixavier\LaravelLibraries\Data\Models\Traits;

use Carbon\Carbon;
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
    /** @var Collection Of meta definitions as [$meta_name => MetaDefinition] */
    protected $meta_definition_objects;

    /** @var Collection Of meta values as [$meta_name => MetaValue] */
    protected $meta_value_objects;

    /**
     * Includes meta values as well
     * @return array
     */
    public function getAllAttributes()
    {
        // @todo: include placement info: parent, children, etc
        return array_merge($this->getMetaValues(), parent::getAttributes());
    }

    /**
     * Sets attributes, respecting mutator methods
     * @param array $values All values. If any value is not in $this->defaultAttributes
     *  they will be saved as meta values. Meta values need to be defined before being set.
     *  {@see setMetaDefinition} to add a meta definition.
     * @param bool $ignore_non_existing_meta If true, will not throw exception when a meta is not defined, will just ignore.
     * @param bool $set_meta_values_too If true, will also set meta values
     * @return void
     * @throws \Exception Validation exception when setting meta values
     */
    public function setAttributes(array $values, bool $ignore_non_existing_meta = true, bool $set_meta_values_too = true): void
    {
        foreach ($this->default_attributes as $defaultAttribute) {
            if (isset($values[$defaultAttribute])) {
                $this->setAttribute($defaultAttribute, $values[$defaultAttribute]);
                unset($values[$defaultAttribute]);
            }
        }
        if ($set_meta_values_too && count($values)) {
            $this->setMetaValues($values, $ignore_non_existing_meta);
        }
    }

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
     * Collection of all meta definitions as [$meta_name => MetaDefinition]
     *
     * @param bool $force If true, will reload data
     * @return Collection
     */
    public function getMetaDefinitionObjects(bool $force = false): Collection
    {
        if (!isset($this->meta_definition_objects) || $force) {
            $this->meta_definition_objects = $this->metaDefinitions()->get()->keyBy('name');
        }
        return $this->meta_definition_objects;
    }

    /**
     * Gets a MetaDefinition object for the given meta name
     *
     * @param string $meta_name Meta name to load
     * @return MetaDefinition
     */
    public function getMetaDefinition(string $meta_name): ?MetaDefinition
    {
        return $this->getMetaDefinitionObjects()->get($meta_name);
    }

    /**
     * Creates in bulk
     * @param Collection $meta_definitions A collection of MetaDefinition objects
     * @param bool $override_existing_meta_definition Override existing meta definition with the ones provided
     */
    public function setMetaDefinitions(Collection $meta_definitions, bool $override_existing_meta_definition = false)
    {
        $new_entries = (new MetaDefinition())->bulkCreate($meta_definitions, $this->type, $override_existing_meta_definition);
        $this->meta_definition_objects = $this->meta_definition_objects->merge($new_entries)->keyBy('name');
    }

    /**
     * Creates/updates a MetaDefinition object for the given meta name
     *
     * @param MetaDefinition $meta_definition Value as MetaDefinition
     * @return void
     */
    public function setMetaDefinition(MetaDefinition $meta_definition): void
    {
        $this->validateIsSaved();

        $meta_name = $meta_definition->name;

        $attributes = $meta_definition->attributesToArray();
        unset($attributes['id']);

        /** @var MetaDefinition $md */
        $md = $this->getMetaDefinition($meta_name) ?? new MetaDefinition();
        $md->setRawAttributes($attributes);

        $md->model_type = $this->type;
        $md->save();
        $this->getMetaDefinitionObjects()->put($meta_name, $md);
    }

    /**
     * @param string $meta_name Meta name to check
     * @return bool
     */
    public function hasMetaDefinition(string $meta_name): bool
    {
        return $this->getMetaDefinition($meta_name) ? true : false;
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
                'value',
                'value_id',
                'model_id',
                'meta_definition_id',
            ]);
    }

    /**
     * Collection of only defined meta values as [$meta_name => MetaDefinition]
     * Use $this->getMetaValueObjects()->get('name')->entry to get actual MetaValue object
     *
     * @param bool $force If true, will reload data
     * @return Collection
     */
    public function getMetaValueObjects(bool $force = false): Collection
    {
        if (!isset($this->meta_value_objects) || $force) {
            $this->meta_value_objects = $this->metaValues()->get()->keyBy('name');
        }
        return $this->meta_value_objects;
    }

    /**
     * All meta values
     * @return array
     */
    public function getMetaValues(): array
    {
        $mvo = $this->getMetaValueObjects();
        $values = [];
        foreach ($mvo as $name => $meta_value) {
            $values[$name] = $meta_value->getValue();
        }
        return $values;
    }

    /**
     * Gets a MetaValue object for the given meta name
     *
     * @param string $meta_name Meta name to load
     * @return MetaValue|null
     */
    public function getMetaValue(string $meta_name): ?MetaValue
    {
        return $this->getMetaValueObjects()->has($meta_name) ? $this->getMetaValueObjects()->get($meta_name) : null;
    }

    /**
     * Object must be saved in db before we can set meta values
     * @param array $values Meta values
     * @param bool $ignore_non_existing_meta
     * @throws \Exception Validation exception
     */
    public function setMetaValues(array $values, bool $ignore_non_existing_meta = true)
    {
        /** @var \Exception $error */
        $error = null;
        // everything else goes into meta
        foreach ($values as $key => $value) {
            try {
                $this->setMetaValue($key, $value);
            } catch (\InvalidArgumentException $e) {
                if (!$ignore_non_existing_meta) {
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
     * Creates/updates a meta value
     *
     * @param string $meta_name Meta name
     * @param MetaValue|mixed $value Value as MetaValue or a scalar
     * @return void
     * @throws \TypeError If $value is not a MetaValue or a scalar
     * @throws \InvalidArgumentException Meta definition not found.
     */
    public function setMetaValue(string $meta_name, $value): void
    {
        $this->validateIsSaved();

        $md = $this->getMetaDefinition($meta_name);
        if ($md) {
            if (!(
                $value instanceof MetaValue ||
                $value instanceof Carbon ||
                is_scalar($value) ||
                (is_array($value) && $md->type === 'json')
            )) {
                $vtype = gettype($value);
                if ($vtype === 'object') {
                    $vtype = 'object(' . get_class($value) . ')';
                }
                throw new \TypeError("Incorrect data type of {$vtype} for value {$md->getUniqueName()}");
            }

            $mv = $this->getMetaValue($meta_name) ?? new MetaValue();
            $mv->model_id = $this->id;
            $mv->meta_definition_id = $md->id;
            $mv->value = $value;
            $mv->save();

            $this->meta_value_objects->put($meta_name, $mv);
            // @todo: May need to do attach or update here
//            $this->metaValues()->attach($mv,  $mv->getAttributes());
        } else {
            throw new \InvalidArgumentException("There is no meta definition for $meta_name");
        }
    }

    /**
     * @param string $meta_name Meta name to check
     * @return bool
     */
    public function hasMetaValue(string $meta_name): bool
    {
        return $this->getMetaValueObjects()->get($meta_name) ? true : false;
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
            $meta_value = $this->getMetaValue($name);
            return $meta_value ? $meta_value->getValue() : null;
        }

        return parent::__get($name);
    }
}
