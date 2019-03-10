<?php

use Sheba\Bkash\ShebaBkash;

Route::get('/', function () {
    $tokenized_payment = (new ShebaBkash())->setModule('tokenized')->getModuleMethod('payment');
    $response = $tokenized_payment->create(\App\Models\Payment::find(9756));
    dd($response);
    return ['code' => 200, 'message' => "Success. This project will hold the api's"];
});
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

$api->version('v1', function ($api) {
    (new App\Http\Route\Prefix\V1\Route())->set($api);
    (new App\Http\Route\Prefix\V2\Route())->set($api);
});
