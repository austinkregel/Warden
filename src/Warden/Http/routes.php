<?php

/**
 * 
 */
Route::group(['prefix' => config('kregel.warden.route'), 'as' => 'warden::'], function () {
  Route::get('{model}/manage/new', ['as' => 'new-model', 'uses' => 'ModelController@getNewModel']);
  Route::post('{model}/manage', ['as' => 'create-model', 'uses' => 'ModelController@createModel']);
  Route::get('{model}s/manage', ['as' => 'models', 'uses' => 'ModelController@getModelList']);
  Route::get('{model}/manage/{id}', ['as' => 'model', 'uses' => 'ModelController@getModel']);
  Route::delete('{model}/manage/{id}', ['as' => 'delete-model', 'uses' => 'ModelController@deleteModel']);
  Route::put('{model}/manage/{id}', ['as' => 'update-model', 'uses' => 'ModelController@updateModel']);


  // Api for basic things
  Route::group(['prefix' => 'api/v1.0', 'as' => 'api.'], function(){
    // Should retrieve almost all items...
    Route::get('{model}s', ['as' => 'get-all', 'uses' => 'ApiController@getAllModels']);
    // Should retrieve some items...
    Route::get('{model}', ['as' => 'get-models', 'uses' => 'ApiController@getSomeModels']);
    // Should get an item...
    Route::get('{model}/{id}', ['as' => 'get-model', 'uses' => 'ApiController@g']);

    // Should create a model
    Route::post('{model}', ['as' => 'create-model', 'uses' => 'ApiController@postModel']);
    // Should update a model.
    Route::put('{model}/{id}', ['as' => 'update-model', 'uses' => 'ApiController@g']);
    // Should delete a model.
    Route::delete('{model}/{id}', ['as' => 'delete-model', 'uses' => 'ApiController@g']);



  });
});
