<?php

namespace Kregel\Warden;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Route;

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
    protected function defineRoutes()
    {
        if (!$this->app->routesAreCached()) {
            $this->mapWebRoutes();

            $this->mapApiRoutes();
        }
    }

    protected function mapWebRoutes()
    {
        if (empty(config('kregel.warden.using.custom-routes'))) {
            Warden::webRoutes();
        }
    }

    protected function mapApiRoutes()
    {
        Route::group([
            'namespace'  => $this->namespace,
            'prefix'     => config('kregel.warden.route').'/api/v1.0',
            'as'         => 'warden::api.',
            'middleware' => config('kregel.warden.auth.middleware_api'),
        ], function ($router) {
            if (empty(config('kregel.warden.using.custom-routes'))) {
                Warden::apiRoutes();
            }
        });
    }
}
