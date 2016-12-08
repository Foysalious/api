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
    $api->post('register-with-facebook', 'App\Http\Controllers\Auth\RegistrationController@registerWithFacebook');
    $api->post('forget-password', 'App\Http\Controllers\Auth\PasswordController@sendResetPasswordEmail');

    $api->group(['prefix' => 'category'], function ($api)
    {
        $api->get('', 'App\Http\Controllers\CategoryController@index');
        $api->get('{category}/children', 'App\Http\Controllers\CategoryController@getChildren');
        $api->get('{category}/parent', 'App\Http\Controllers\CategoryController@getParent');
    });
    $api->group(['prefix' => 'service'], function ($api)
    {
        $api->get('{service}/partners', 'App\Http\Controllers\ServiceController@getPartners');
        $api->get('{service}/location/{location}/partners', 'App\Http\Controllers\ServiceController@getPartners');
        $api->post('{service}/change-partner', 'App\Http\Controllers\ServiceController@changePartnerWithoutLocation');
        $api->post('{service}/{location?}/change-partner', 'App\Http\Controllers\ServiceController@changePartner');
    });
    $api->group(['prefix' => 'partner'], function ($api)
    {
        $api->get('{partner}/services', 'App\Http\Controllers\PartnerController@getPartnerServices');
    });

    $api->group(['prefix' => 'checkout'], function ($api)
    {
        $api->post('place-order', 'App\Http\Controllers\CheckoutController@placeOrder');
        $api->post('place-order-with-online-payment', 'App\Http\Controllers\CheckoutController@placeOrderWithPayment');
        $api->get('place-order-final', 'App\Http\Controllers\CheckoutController@placeOrderFinal');
        $api->get('sp-payment-final', 'App\Http\Controllers\CheckoutController@spPaymentFinal');
    });

    $api->group(['prefix' => 'customer', 'middleware' => 'customer.auth'], function ($api)
    {
        $api->get('{customer}', 'App\Http\Controllers\CustomerController@getCustomerInfo');
        $api->post('{customer}/fb-integration', 'App\Http\Controllers\CustomerController@facebookIntegration');
        $api->post('{customer}/change-address', 'App\Http\Controllers\CustomerController@changeAddress');
        $api->post('{customer}/add-delivery-address', 'App\Http\Controllers\CustomerController@addDeliveryAddress');
        $api->post('{customer}/mobile', 'App\Http\Controllers\CustomerController@modifyMobile');
        $api->post('{customer}/add-secondary-mobile', 'App\Http\Controllers\CustomerController@addSecondaryMobile');
        $api->post('{customer}/remove-secondary-mobile', 'App\Http\Controllers\CustomerController@removeSecondaryMobile');
        $api->post('{customer}/set-primary-mobile', 'App\Http\Controllers\CustomerController@setPrimaryMobile');
        $api->post('{customer}/email', 'App\Http\Controllers\CustomerController@modifyEmail');
        $api->post('{customer}/general-info', 'App\Http\Controllers\CustomerController@modifyGeneralInfo');
        $api->post('{customer}/send-verification-link', 'App\Http\Controllers\CustomerController@sendVerificationLink');
        $api->post('{customer}/order-list', 'App\Http\Controllers\OrderController@getNotClosedOrderInfo');
        $api->post('{customer}/order-history', 'App\Http\Controllers\OrderController@getClosedOrderInfo');
        $api->post('{customer}/sp-payment', 'App\Http\Controllers\CheckoutController@spPayment');
    });

    $api->group(['prefix' => 'job'], function ($api)
    {
        $api->get('{job}', 'App\Http\Controllers\JobController@getInfo');
    });
});