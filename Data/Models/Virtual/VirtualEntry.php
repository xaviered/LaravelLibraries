<?php

namespace ixavier\LaravelLibraries\Data\Models\Virtual;

/**
 * Class VirtualEntry mimics a real data entry, but only lives on virtual memory
 */
abstract class VirtualEntry
{
    /**
     * Constructor.
     * @param array $attributes Object attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->hydrate($attributes);
    }

    /**
     * @param array $attributes Object attributes
     */
    public function hydrate(array $attributes): void
    {
        foreach (get_class_vars(static::class) as $class_var) {
            if (isset($attributes[$class_var])) {
                $this->{$class_var} = $attributes[$class_var];
            }
        }
    }
}
