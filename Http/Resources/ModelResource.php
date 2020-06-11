<?php

namespace ixavier\LaravelLibraries\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use ixavier\LaravelLibraries\Data\Models\Model;

/**
 * Resource representation for a Model
 * @property Model $resource The resource being used
 */
class ModelResource extends JsonResource
{
    /**
     * Transform the resource into an array.
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
            'parent' => $this->resource->parent(),
            'children' => $this->resource->children(),
        ];
    }
}
