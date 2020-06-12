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
        $models = Model::search(['type' => $type, 'alias_id' => null]);
        return new ModelResourceCollection($models);
    }

    /**
     * @param string $type Type of model
     * @param int $id ID of model
     * @return ModelResource
     */
    public function view(string $type, int $id): ModelResource
    {
        return new ModelResource(Model::searchOneOrFail(['id' => $id, 'type' => $type]));
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
        return new ModelResource($model->create($params));
    }

    public function update(string $type, int $id): ModelResource
    {
        $params = Request::capture()->all();
        $model = Model::searchOneOrFail(['id' => $id, 'type' => $type]);
        $params['id'] = $id;
        $params['type'] = $params['type'] ?? $type;
        $model->updateOrFail($params);
        return new ModelResource($model);
    }
}
