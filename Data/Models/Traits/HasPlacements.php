<?php

namespace ixavier\LaravelLibraries\Data\Models\Traits;

use ixavier\LaravelLibraries\Data\Models\Placement;

/**
 * Trait HasPlacements contains all placement functionality
 */
trait HasPlacements
{
    /**
     * Related placements
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function placements()
    {
        return $this->hasMany(
            Placement::class,
            'model_id',
            'id'
        );
    }
}
