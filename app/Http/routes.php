<?php
Route::get('/', function ()
{
    return redirect('http://localhost:8080/service/6/jhfjf');
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

    $api->group(['prefix' => 'category/'], function ($api)
    {
        $api->get('', 'App\Http\Controllers\CategoryController@index');
        $api->get('{category}/children', 'App\Http\Controllers\CategoryController@getChildren');
        $api->get('{category}/parent', 'App\Http\Controllers\CategoryController@getParent');
    });
    $api->group(['prefix' => 'service/'], function ($api)
    {
        $api->get('{service}/location/{location}/partners', 'App\Http\Controllers\ServiceController@getPartners');
        $api->post('{service}/location/{location}/change-partner', 'App\Http\Controllers\ServiceController@changePartner');
    });
    $api->group(['prefix' => 'partner/'], function ($api)
    {
        $api->get('{partner}/services', 'App\Http\Controllers\PartnerController@getPartnerServices');
    });

    $api->group(['prefix' => 'checkout/'], function ($api)
    {
        $api->post('place-order', 'App\Http\Controllers\CheckoutController@placeOrder');
        $api->post('place-order-with-online-payment', 'App\Http\Controllers\CheckoutController@placeOrderWithPayment');
        $api->get('place-order-final', 'App\Http\Controllers\CheckoutController@placeOrderFinal');
    });
});