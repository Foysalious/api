<?php
Route::get('/', function ()
{
    return ['code' => 200, 'msg' => 'Success. This project will hold the api\'s'];
});

Route::get('email-verification/{customer}/{code}', 'CustomerController@emailVerification');
Route::get('reset-password/{customer}/{code}', 'PasswordController@getResetPasswordForm');
Route::post('reset-password/{customer}/{code}', 'PasswordController@resetPassword');
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
    $api->post('login', 'App\Http\Controllers\Auth\LoginController@login');
    $api->post('login-with-kit', 'App\Http\Controllers\Auth\LoginController@loginWithKit');
    $api->post('register-mobile', 'App\Http\Controllers\Auth\RegistrationController@registerWithMobile');
    $api->post('register-email', 'App\Http\Controllers\Auth\RegistrationController@registerWithEmail');
    $api->post('forget-password', 'App\Http\Controllers\Auth\PasswordController@sendResetPasswordEmail');

    $api->get('category', 'App\Http\Controllers\CategoryController@index');
    $api->get('category/{category}/children', 'App\Http\Controllers\CategoryController@getChildren');
    $api->get('category/{category}/parent', 'App\Http\Controllers\CategoryController@getParent');

    $api->get('service/{service}/{name}', 'App\Http\Controllers\ServiceController@show');
});