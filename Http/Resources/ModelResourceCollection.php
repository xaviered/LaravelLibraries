<?php

namespace ixavier\LaravelLibraries\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class ModelResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return parent::toArray($request);
    }

    /**
     * Additional data for this resource
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function with($request)
    {
        $first = $this->collection->first();
        return [
            'parent' => !empty($first) ? $first->parent(): null,
        ];
    }
}
