<?php

namespace ixavier\LaravelLibraries\Data\Models\Traits;

use Illuminate\Support\Collection;
use ixavier\LaravelLibraries\Data\Models\MetaDefinition;
use ixavier\LaravelLibraries\Data\Models\Relationships\MetaValue;

trait HasMeta2
{
    /** @var Collection Of meta definitions as [$metaName => MetaDefinition] */
    protected $metaDefinitions;

    /** @var Collection Of meta values as [$metaName => MetaValue] */
    protected $metaValues;

    /**
     * Loads meta info for this model
     * @param bool $force If true, do not load from cache
     * @return $this Chain
     */
    private function loadMeta(bool $force = false): self
    {
        if (!isset($this->metaDefinitions) || $force) {
            $this->metaDefinitions = MetaDefinition::query()->find(['model_id' => $this->id])->keyBy('name');

            $this->metaValues = new Collection();
            $mv_col = MetaValue::query()->find(['model_id' => $this->id])->keyBy('meta_definition_id');

            // only get meta values that are defined, as a precaution
            foreach ($this->metaDefinitions as $md) {
                $mv = $mv_col->get($md->id);
                if ($mv) {
                    $this->metaValues[$md->name] = $mv;
                }
            }
        }

        return $this;
    }

    /**
     * Gets a MetaValue object for the given meta name
     *
     * @param string $metaName Meta name to load
     * @param mixed $default If nothing found, will return this value
     * @return MetaValue|mixed
     */
    public function getMetaValue(string $metaName, $default = null)
    {
        $this->loadMeta();
        return $this->metaValues->get($metaName, $default);
    }

    /**
     * Gets a MetaValue object for the given meta name
     *
     * @param string $metaName Meta name to load
     * @param MetaValue|mixed $value Value as MetaValue or a scalar
     * @return void
     */
    public function setMetaValue(string $metaName, $value)
    {
        $this->loadMeta();
        $md = $this->metaDefinitions->get($metaName);
        if ($md) {
            if ($value instanceof MetaValue) {
                $mv = $value;
            } else if (is_scalar($value)) {
                $mv = $this->metaValues->get($metaName);
                if (!$mv) {
                    $mv = new MetaValue(['value' => $value]);
                }
            } else {
                throw new \InvalidArgumentException("Value needs to be a MetaValue or a scalar");
            }

            $mv->model_id = $this->id;
            $mv->meta_definition_id = $md->id;
            $mv->save();
            $this->metaValues->put($metaName, $mv);
        }
    }

    /**
     * @param string $metaName Meta name to check
     * @return bool
     */
    public function hasMetaValue(string $metaName): bool
    {
        return $this->metaValues->has($metaName);
    }

    /**
     * Gets a MetaDefinition object for the given meta name
     *
     * @param string $metaName Meta name to load
     * @param mixed $default If nothing found, will return this value
     * @return MetaDefinition|mixed
     */
    public function getMetaDefinition(string $metaName, $default = null)
    {
        $this->loadMeta();
        return $this->metaDefinitions->get($metaName, $default);
    }

    /**
     * Creates a MetaDefinition object for the given meta name
     *
     * @param MetaDefinition $metaDefinition Value as MetaDefinition
     * @return void
     */
    public function putMetaDefinition(MetaDefinition $metaDefinition)
    {
        $this->loadMeta();
        $metaName = $metaDefinition->name;

        /** @var MetaDefinition $md */
        $md = $this->metaDefinitions->get($metaName);
        if ($md) {
            $attributes = $metaDefinition->attributesToArray();
            unset($attributes['id']);
            // @todo: maybe also unset timestamps
            $md->setRawAttributes($attributes);
        } else {
            $md = $metaDefinition;
        }

        $md->model_id = $this->id;
        $md->save();
        $this->metaDefinitions->put($metaName, $metaDefinition);
    }

    /**
     * @param string $metaName Meta name to check
     * @return bool
     */
    public function hasMetaDefinition(string $metaName): bool
    {
        return $this->metaDefinitions->has($metaName);
    }

    /**
     * Magic method for dynamically setting a meta value
     *
     * @param string $name Key of variable
     * @param mixed $value Value of variable
     */
    public function __set($name, $value)
    {
        $this->loadMeta();
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
        $this->loadMeta();
        if ($this->hasMetaDefinition($name)) {
            return $this->getMetaValue($name);
        }

        return parent::__get($name);
    }
}
