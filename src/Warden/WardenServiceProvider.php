<?php

namespace Kregel\Warden;

use Illuminate\Routing\Router;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class WardenServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    protected $namespace = 'Kregel\Warden\Http\Controllers';

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
	parent::boot($router);

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'warden');
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/warden'),
        ], 'views');
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('kregel/warden.php'),
        ], 'config');

        // Define our custom authentication to make
        // sure that the user is logged in!
        $this->app['router']->middleware('custom-auth', config('kregel.warden.auth.middleware'));
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $this->mapWebRoutes($router);
        //
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    protected function mapWebRoutes(Router $router)
    {
        $router->group(['namespace' => $this->namespace, 'middleware' => 'web'], function ($router) {
            require __DIR__ .('/Http/routes.php');
        });
        $router->group(['namespace' => $this->namespace, 'middleware' => 'api'], function ($router) {
            require __DIR__ .('/Http/api_routes.php');
        });

    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
