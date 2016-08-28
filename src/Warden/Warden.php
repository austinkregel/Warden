<?php

namespace Kregel\Warden;

use Route;

class Warden
{
    /**
     * This will look through the input and remove any unset values.
     *
     * @param $input
     *
     * @return mixed
     */
    public static function clearInput($input)
    {
        if (is_array($input)) {
            foreach ($input as $key => $value) {
                if (!isset($value) || $value === '') {
                    unset($input[$key]);
                }
            }

            return $input;
        }

        return $input;
    }

    /**
     * @return string
     */
    public static function generateUUID()
    {
        $data = openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    /**
     * Define the api routes more dynamically.
     */
    public static function apiRoutes()
    {
        Route::group([
            'namespace'  => 'Kregel\\Warden\\Http\\Controllers',
            'prefix'     => config('kregel.warden.route').'/api/v1.0',
            'as'         => 'warden::api.',
            'middleware' => config('kregel.warden.auth.middleware_api'),
        ], function ($router) {
            // Should retrieve almost all items...
            Route::get('{model}s', ['as' => 'get-all', 'uses' => 'ApiController@getAllModels']);
            // Should retrieve some items...
            Route::get('{model}', ['as' => 'get-models', 'uses' => 'ApiController@getSomeModels']);
            // Should get an item...
            Route::get('{model}/{id}', ['as' => 'get-model', 'uses' => 'ApiController@findModel']);

            // Should create a model using /warden/api/v1.0/user/
            Route::post('{model}', ['as' => 'create-model', 'uses' => 'ApiController@postModel']);

            // Should update a model.
            Route::post('{model}/{id}', ['as' => 'update-model-post', 'uses' => 'ApiController@putModel']);
            Route::put('{model}/{id}', ['as' => 'update-model', 'uses' => 'ApiController@putModel']);

            // Should delete a model.
            Route::delete('d/{model}/{id}', ['as' => 'delete-model', 'uses' => 'ApiController@deleteModel']);
        });
    }

    /**
     * Define the web routes more dynamically.
     */
    public static function webRoutes()
    {
        Route::group([
            'namespace'  => 'Kregel\\Warden\\Http\\Controllers',
            'prefix'     => config('kregel.warden.route'),
            'as'         => 'warden::',
            'middleware' => config('kregel.warden.auth.middleware'),
        ], function ($router) {
            Route::get('/', function () {
                return view('warden::base');
            });

            Route::get('{model}/manage/new', ['as' => 'new-model', 'uses' => 'ModelController@getNewModel']);
            Route::get('{model}s/manage', ['as' => 'models', 'uses' => 'ModelController@getModelList']);
            Route::get('{model}/manage/{id}', ['as' => 'model', 'uses' => 'ModelController@getModel']);
        });
    }
}
