<?php

namespace ixavier\LaravelLibraries\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ModelTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $output = [];
        foreach ($this->resource as $resource) {
            $output[$resource] = route('api.model.list', $resource);
        }
        return $output;
    }
}
