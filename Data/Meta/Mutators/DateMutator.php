<?php

namespace ixavier\LaravelLibraries\Data\Meta\Mutators;

use Carbon\Carbon;

/**
 * Class changes data to type Carbon
 */
class DateMutator extends BaseMutator
{
    /**
     * @return string Ready for data store
     */
    public function serialize()
    {
        return $this->value->toDateTimeString();
    }

    /**
     * @return Carbon
     */
    public function deserialize(): ?Carbon
    {
        return Carbon::createFromTimeString($this->value);
    }
}
