<?php

namespace ixavier\LaravelLibraries\Providers;

use Illuminate\Support\ServiceProvider;
use ixavier\LaravelLibraries\Data\Models\ModelLoader;

class ModelProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ModelLoader::class, function ($app) {
            return new ModelLoader();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
