<?php

/**
 *
 */
Route::group(['prefix' => config('kregel.warden.route'), 'as' => 'warden::', 'middleware' => config('kregel.warden.auth.middleware')], function () {
    Route::get('/', function () {
        return view('warden::base');
    });

    Route::get('{model}/manage/new', ['as' => 'new-model', 'uses' => 'ModelController@getNewModel']);
    Route::get('{model}s/manage', ['as' => 'models', 'uses' => 'ModelController@getModelList']);
    Route::get('{model}/manage/{id}', ['as' => 'model', 'uses' => 'ModelController@getModel']);

    // Api for basic things
    Route::group(['prefix' => 'api/v1.0', 'as' => 'api.'], function () {
        // Should retrieve almost all items...
        Route::get('{model}s', ['as' => 'get-all', 'uses' => 'ApiController@getAllModels']);
        // Should retrieve some items...
        Route::get('{model}', ['as' => 'get-models', 'uses' => 'ApiController@getSomeModels']);
        // Should get an item...
        Route::get('{model}/{id}', ['as' => 'get-model', 'uses' => 'ApiController@findModel']);

        // Should create a model using /warden/api/v1.0/user/
        Route::post('{model}', ['as' => 'create-model', 'uses' => 'ApiController@postModel']);

        // Should update a model.
        Route::post('{model}/{id}', ['as' => 'update-model', 'uses' => 'ApiController@putModel']);
        Route::put('{model}/{id}', ['as' => 'update-model', 'uses' => 'ApiController@putModel']);

        // Should delete a model.
        Route::delete('{model}/{id}', ['as' => 'delete-model', 'uses' => 'ApiController@deleteModel']);
    });
});
