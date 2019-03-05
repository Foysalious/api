<?php

use Sheba\TopUp\TopUp;
use Sheba\TopUp\Vendor\VendorFactory;

Route::get('/', function () {
    $top_up_request = new \Sheba\TopUp\TopUpRequest();
    $top_up_request->setAmount(10)->setMobile('+8801678242973')->setType('postpaid');

    $vendor_factory = app(VendorFactory::class);
    $vendor = $vendor_factory->getById(3);

    $topUp = app(TopUp::class);
    $agent = \App\Models\Affiliate::find(595);
    $topUp->setAgent($agent)->setVendor($vendor);

    $topUp->recharge($this->topUpRequest);
    dd($topUp->isNotSuccessful());

    if ($topUp->isNotSuccessful()) {
        $this->takeUnsuccessfulAction();
    } else {
        $this->takeSuccessfulAction();
    }

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
