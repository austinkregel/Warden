<?php
Route::get('/', function () {
    return view('warden::base');
});

Route::get('{model}/manage/new', ['as' => 'new-model', 'uses' => 'ModelController@getNewModel']);
Route::get('{model}s/manage', ['as' => 'models', 'uses' => 'ModelController@getModelList']);
Route::get('{model}/manage/{id}', ['as' => 'model', 'uses' => 'ModelController@getModel']);
