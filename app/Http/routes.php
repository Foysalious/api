<?php

Route::get('/', function () {

    $top_up_order = \App\Models\TopUpOrder::find();
    app(\Sheba\TopUp\TopUp::class)->setAgent($this->agent)
        ->setVendor(app(\Sheba\TopUp\Vendor\VendorFactory::class)->getById($this->vendorId))
        ->recharge($top_up_order);

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
    (new App\Http\Route\Prefix\V3\Route())->set($api);
});
