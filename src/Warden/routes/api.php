<?php

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
