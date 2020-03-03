<?php

namespace ixavier\LaravelLibraries\Data\Models\Relationships;

use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class MetaValue holds a value for a given model meta
 */
class MetaValue extends Pivot
{
    /** @var string Table name */
    protected $table = 'meta_values';

    /** @var bool Indicates if the IDs are auto-incrementing */
    public $incrementing = true;
}
