<?php

Route::get('/', function ()
{
    return ['code' => 200, 'msg' => 'Success. This project will hold the api\'s'];
});

Route::get('email-verification/{customer}/{code}', 'CustomerController@emailVerification');


$api = app('Dingo\Api\Routing\Router');

/*
|--------------------------------------------------------------------------
| Version Reminder
|--------------------------------------------------------------------------
|
| When next version comes add a prefix to the old version
| routes and change API_PREFIX in api.php file to null
|
|
*/
$api->version('v1', function ($api)
{
    $api->post('login', 'App\Http\Controllers\Auth\AuthController@login');
    $api->post('login-with-kit', 'App\Http\Controllers\Auth\AuthController@loginWithKit');
    $api->post('register-mobile', 'App\Http\Controllers\Auth\AuthController@registerWithMobile');
    $api->post('register-email', 'App\Http\Controllers\Auth\AuthController@registerWithEmail');

    //Routes that require authentication
    $api->group(['middleware' => 'api.auth'], function ($api)
    {
        $api->get('refresh', function ()
        {
            return "refresh";
        });
    });

});