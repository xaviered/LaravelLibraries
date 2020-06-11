<?php

namespace ixavier\LaravelLibraries\Http\Controllers;

use ixavier\LaravelLibraries\Data\Models\Model;
use ixavier\LaravelLibraries\Http\Resources\ModelResource;
use ixavier\LaravelLibraries\Http\Resources\ModelResourceCollection;
use ixavier\LaravelLibraries\Http\Resources\ModelTypeResource;

abstract class ModelController extends BaseController
{
    /**
     * Lists all model types on system
     * @return ModelTypeResource
     */
    public function types()
    {
        $types = Model::query()
            ->select('type')
            ->orderBy('type')
            ->groupBy('type')
            ->get(['type'])
            ->pluck('type');

        // @todo: Add new resource for this
        return new ModelTypeResource($types);
    }

    /**
     * Lists all models of type $resource
     * @param string $resource
     * @return \ixavier\LaravelLibraries\Http\Resources\ModelResourceCollection|null
     */
    public function list(string $type)
    {
        $models = Model::query()->where('type', '=', $type)->get();
        return new ModelResourceCollection($models);
    }

    /**
     * @param string $type Type of model
     * @param int $id ID of model
     * @return ModelResource
     */
    public function view(string $type, int $id)
    {
        $model = Model::query()
            ->where('id', '=', $id)
            // for security purposes :shrug:
            ->where('type', '=', $type)
            ->firstOrFail();
        return new ModelResource($model);
    }
}
