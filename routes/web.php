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

$pageSize = 5;


Route::get('/', function () {
    return view('welcome');
});

Route::get('/test', 'DataController@test');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('private/users', function () {
    return App\User::paginate(5);
});



Route::group(['prefix' => 'private', 'middleware' => 'auth'], function () use ($router, $pageSize) {
    $router->get('user/info', 'UsersController@info');

    $router->get('users/list', function () use($pageSize) {
        // 匹配 "/admin/users" URL
         return [
             'code' => 0,
             'data' => App\User::paginate($pageSize)->toArray()
         ];
    });
    $router->post('users/create', 'UsersController@createUser');
    $router->post('users/active', 'UsersController@activeAccount');
    $router->post('users/resetSelfPwd', 'UsersController@resetSelfPwd');

    $router->post('category/create', 'DataController@postCategory');
    $router->post('category/delete', 'DataController@deleteCategory');

    $router->get('category/list', function () use ($pageSize) {
        return [
            'code' => 0,
            'data' => App\Model\Category::paginate(16)->toArray()
        ];
    });


    $router->get('category/query', 'DataController@queryCategory');

//    baseinfo
    $router->post('avatar/upload', 'BasicInfoController@upload');
    $router->post('baseinfo/save', 'BasicInfoController@saveInfo');
    $router->post('/basicinfo/delete', 'BasicInfoController@deleteInfo');

    $router->get('basicinfo/list', 'BasicInfoController@queryBasicInfo');




});