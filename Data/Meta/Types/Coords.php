<?php

namespace ixavier\LaravelLibraries\Data\Meta\Types;

class Coords
{
    /** @var int X value */
    public $x;
    /** @var int Y value */
    public $y;
    /** @var int Z value */
    public $z;

    /**
     * Coords constructor.
     * @param array $coords Array of coords as in this format/order:
     *  [x => x number, y => y number, z => z number]
     *  [x number, y number, z number]
     */
    public function __construct(array $coords)
    {
        $this->x = $coords['x'] ?? $coords[0] ?? null;
        $this->y = $coords['y'] ?? $coords[1] ?? null;
        $this->z = $coords['z'] ?? $coords[2] ?? null;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'x' => $this->x,
            'y' => $this->y,
            'z' => $this->z,
        ];
    }
}
