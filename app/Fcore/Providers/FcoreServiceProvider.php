<?php

namespace App\Fcore\Providers;

use App\Fcore\Facades\Fast;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class FcoreServiceProvider extends ServiceProvider
{
    protected $routeMiddleware = [
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
    ];


    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/../Views', 'Fcore');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('Fast', Fast::class);
        });
        $this->registerRouteMiddleware();
    }

    /**
     * Register the route middleware.
     *
     * @return void
     */
    protected function registerRouteMiddleware()
    {
        // register route middleware.
        foreach ($this->routeMiddleware as $key => $middleware) {
            app('router')->middleware($key, $middleware);
        }

        // register middleware group.
        foreach ($this->middlewareGroups as $key => $middleware) {
            app('router')->middlewareGroup($key, $middleware);
        }
    }

}
