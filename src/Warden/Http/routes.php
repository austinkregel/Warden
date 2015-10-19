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
});
