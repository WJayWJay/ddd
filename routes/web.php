<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', 'DataController@test');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('private/users', function () {
    return App\User::paginate(5);
});

Route::group(['prefix' => 'private', 'middleware' => 'auth'], function () use ($router) {
    $router->get('users/list', function ()    {
        // 匹配 "/admin/users" URL
        return App\User::paginate(5);
    });
});