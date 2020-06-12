<?php

namespace ixavier\LaravelLibraries\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent;
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
    public function types(): ModelTypeResource
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
    public function list(string $type): ModelResourceCollection
    {
        $models = Model::search(['type' => $type]);
        return new ModelResourceCollection($models);
    }

    /**
     * @param string $type Type of model
     * @param int $id ID of model
     * @return ModelResource
     */
    public function view(string $type, int $id): ModelResource
    {
        /** @var Eloquent\Builder $q */
        list($q, $model_query, $meta_query) = static::prepareSearchQuery(['id' => $id, 'type' => $type]);
        return new ModelResource($q->firstOrFail());
    }

    public function search(): ModelResourceCollection
    {
        $models = Model::search(['first_name' => 'Edison']);
        return new ModelResourceCollection($models);
    }

    public function create(string $type): ModelResource
    {
        $params = Request::capture()->all();
        $model = new Model();
        $model->create($params);
        dd($params);
    }
}
