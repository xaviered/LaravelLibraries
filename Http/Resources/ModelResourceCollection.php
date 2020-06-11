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
        return [
            'parent' => $this->collection->first()->parent(),
        ];
    }
}
