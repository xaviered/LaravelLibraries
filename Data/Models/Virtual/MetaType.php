<?php

namespace ixavier\LaravelLibraries\Data\Models\Virtual;

use Illuminate\Support\Collection;

/**
 * Class MetaDefinition holds a meta definition for a given model
 */
class MetaType extends VirtualEntry
{
    /** @var Collection of MetaType defined by site */
    private static $all_definitions;

    /** @var string Encoding */
    public $encoding = 'string';

    /** @var string Cast type */
    public $cast = 'string';

    /** @var string Template to render this value */
    public $template = 'string';

    /** @var bool Can it be nullable */
    public $nullable = true;

    /**
     * Gets a collection of MetaType defined by site
     * @param bool $reload If true, will bust cache
     * @return Collection
     */
    public static function getAllFromConfigs(bool $reload = false): Collection
    {
        if (!isset(self::$all_definitions) || $reload) {
            self::$all_definitions = new Collection();
            $configs = array_merge(config('schema.meta'), config('site-schema.meta') ?? []);

            foreach ($configs as $config) {
                self::$all_definitions->add(new self($config));
            }
        }
        return self::$all_definitions;
    }
}
