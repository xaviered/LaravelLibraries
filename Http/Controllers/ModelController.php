<?php

namespace ixavier\LaravelLibraries\Http\Controllers;

use ixavier\LaravelLibraries\Data\Models\ModelLoader;

abstract class ModelController extends BaseController
{
    public function view(ModelLoader $ml, string $resource, int $id) {
        $model = $ml->getModelType($resource)->newModelQuery()->find($id);
        return $model->getResource();
    }

    public function listView(string $resource) {

        $model = ModelLoader::getModelType($resource);
        return $model->getResourceCollection();
    }
}
