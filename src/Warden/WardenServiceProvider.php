<?php

namespace Kregel\Warden;

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
     * Register the service provider.
     */
    public function register()
    {
        // Register the FormModel Provider.
        $this->app->register(\Kregel\FormModel\FormModelServiceProvider::class);
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        // Register the alias.
        $loader->alias('FormModel', Kregel\FormModel\FormModel::class);
    }

  /**
   * Bootstrap any application services.
   *
   * @return void
   */
  public function boot()
  {
      $this->app->booted(function () {
          $this->defineRoutes();
      });

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'warden');
        $this->publishes([
            __DIR__.'/../resources/views' => base_path('resources/views/vendor/warden'),
        ], 'views');
        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('kregel/warden.php'),
        ], 'config');
        // Define our custom authentication to make sure
        // that the user is logged in!
        $this->publishes([
          __DIR__.'/../resources/views' => base_path('resources/views/vendor/warden'),
        ], 'views');
        // Define our custom authentication to make sure
        // that the user is logged in!
        $this->app['router']->middleware('custom-auth', config('kregel.warden.auth.middleware'));
    }

    /**
    * Define the UserManagement routes.
    */
    protected function defineRoutes()
    {
        if (!$this->app->routesAreCached()) {
            $router = app('router');

            $router->group(['namespace' => 'Kregel\\Warden\\Http\\Controllers'], function ($router) {
                require __DIR__.'/Http/routes.php';
            });
        }
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
