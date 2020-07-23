<?php

use App\Models\Customer;
use App\Models\PartnerOrder;
use Sheba\AutoSpAssign\Initiator;

Route::get('/', function () {
    $p_order = PartnerOrder::find(183985);
    $customer = Customer::find(6582);
    /** @var Initiator $initiator */
    $initiator = app(Initiator::class);
    $initiator->setPartnerIds([32486, 2969, 3836, 2281])->setCustomer($customer)->setPartnerOrder($p_order)->initiate();
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
