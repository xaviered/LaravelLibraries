<?php

namespace ixavier\LaravelLibraries\Data\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DataEntry is base class for all DB entries.
 */
abstract class DataEntry extends Model
{
    /**
     * Helper function to get table name statically
     * @return string
     */
    public static function getTableName()
    {
        return (new static)->getTable();
    }
}
