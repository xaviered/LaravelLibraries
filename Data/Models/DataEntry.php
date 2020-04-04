<?php

namespace ixavier\LaravelLibraries\Data\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class DataEntry is base class for all DB entries.
 */
abstract class DataEntry extends Model
{
    /**
     * 15 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
     * @var int Encoding options for JSON
     */
    public const DEFAULT_ENCODING_OPTIONS = 15;
}
