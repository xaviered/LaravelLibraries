<?php

namespace ixavier\LaravelLibraries\Data\Meta\Mutators;

use ixavier\LaravelLibraries\Data\Meta\Types\Coords;

/**
 * Class mutates data to JSON
 */
class CoordsMutator extends JsonMutator
{
    /**
     * @return string Ready for data store
     */
    public function serialize()
    {
        $value = $this->value;
        if ($value instanceof Coords) {
            $value = $this->value->toArray();
        } else if (!is_array($value)) {
            throw new \InvalidArgumentException("Value must be a " . Coords::class . " object or array");
        }

        return $this->toJson($value);
    }

    /**
     * @return Coords|null Coords object
     */
    public function deserialize(): ?Coords
    {
        $value = $this->fromJson((string) $this->value);
        if ($value) {
            return new Coords($value);
        }
        return null;
    }
}
