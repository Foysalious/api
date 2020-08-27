<?php

use App\Models\Customer;
use App\Models\PartnerOrder;
use Sheba\AutoSpAssign\Initiator;

Route::get('/', function () {
    $instance_id = 'demo_instance_id';
    try {
        $instance_id = file_get_contents("http://instance-data/latest/meta-data/instance-id");
    } catch (Exception $e) {};
    $p_order = PartnerOrder::find(183985);
    $customer = Customer::find(6582);
    /** @var Initiator $initiator */
    $initiator = app(Initiator::class);
    $initiator->setPartnerIds([32486, 2969, 3836, 2281])->setCustomer($customer)->setPartnerOrder($p_order)->initiate();
    return ['code' => 200, 'message' => "Success. This project will hold the api's. Instance ID: " . $instance_id];
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
