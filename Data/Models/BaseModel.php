<?php

namespace ixavier\LaravelLibraries\Data\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

abstract class BaseModel extends Model
{
    use SoftDeletes;

    /** @var string Column name for created timestamp */
    const CREATED_AT = 'created_at';

    /** @var string Column name for updated timestamp */
    const UPDATED_AT = 'updated_at';

    /**
     * The storage format of the model's date columns.
     *
     * @var string
     */
    protected $dateFormat = 'U';
}
