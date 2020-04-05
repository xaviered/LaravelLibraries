<?php

namespace ixavier\LaravelLibraries\Data\Meta\Mutators;

/**
 * Class changes data to type float
 */
class NumberMutator extends BaseMutator
{
    /**
     * @return float|mixed
     */
    public function deserialize(): float
    {
        return (float) $this->value;
    }
}
