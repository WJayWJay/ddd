<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->get('/api/user', ['middleware'=> 'auth', function (Request $request) use ($router) {
    return response('aaa');
}]);


$router->group(['middleware' => 'auth', 'prefix' => 'api'], function () use ($router) {
    $router->get('users', function () {
        return response('hello world');
    });

    $router->get('info', 'UserController@info');
});

$router->post('/public/login', 'UserController@login');
$router->post('/public/register', 'UserController@register');

$router->get('/test', 'UserController@test');