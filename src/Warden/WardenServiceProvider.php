<?php

namespace Kregel\Warden;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class WardenServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Indicates the namespace of this package's routing.
     *
     * @var string
     */
    protected $namespace = 'Kregel\\Warden\\Http\\Controllers';

    /**
     * Register the service provider.
     */
    public function register()
    {
        // Register the FormModel Provider.
        $this->app->register(\Kregel\FormModel\FormModelServiceProvider::class);
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        // Register the alias.
        $loader->alias('FormModel', \Kregel\FormModel\FormModel::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(Router $router)
    {
        $this->defineRoutes($router);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'warden');
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/warden'),
        ], 'views');
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('kregel/warden.php'),
        ], 'config');
    }

    /**
     * Define the UserManagement routes.
     */
    protected function defineRoutes(Router $router)
    {
        if (!$this->app->routesAreCached()) {
            $this->mapWebRoutes($router);

            $this->mapApiRoutes($router);
        }
    }

    protected function mapWebRoutes($router)
    {
        $router->group([
            'namespace'  => $this->namespace,
            'prefix'     => config('kregel.warden.route'),
            'as'         => 'warden::',
            'middleware' => config('kregel.warden.auth.middleware'),
        ], function () use ($router) {
            require __DIR__.'/routes/web.php';
        });
    }

    protected function mapApiRoutes($router)
    {
        $router->group([
            'namespace'  => $this->namespace,
            'prefix'     => config('kregel.warden.route').'/api/v1.0',
            'as'         => 'warden::api.',
            'middleware' => config('kregel.warden.auth.middleware_api'),
        ], function ($router) {
            require __DIR__.'/routes/api.php';
        });
    }
}
