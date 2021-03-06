<?php

/*
|--------------------------------------------------------------------------
| Routes File
|--------------------------------------------------------------------------
|
| Here is where you will register all of the routes in an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/

Route::group(['middleware' => ['web']], function () {
    //
});
Route::put('items/{id}/repeat', 'ItemsController@repeat');
Route::get('items/next-to-repeat', 'ItemsController@next_to_repeat');
Route::get('items/learn', 'ItemsController@learn');
Route::get('items/inactive', 'ItemsController@inactive');
Route::get('items/stats', 'ItemsController@stats');
Route::get('items/search/{query}', 'ItemsController@search');
Route::resource('items', 'ItemsController');
Route::post('users/auth', 'UsersController@auth');
Route::get('items/{id}/links', 'ItemsController@get_links');
Route::get('items/{id}/backlinks', 'ItemsController@get_backlinks');
Route::put('items/{id}/links', 'ItemsController@put_links');
Route::delete('items/{id}/links', 'ItemsController@delete_link');