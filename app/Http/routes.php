<?php

use App\Models\Order;

Route::get('/', function () {
    $orders = Order::whereHas('partnerOrders', function ($q) {
        $q->where('partner_id', null);
    })->whereRaw('created_at + INTERVAL 15 MINUTE <= NOW()')->with('partnerOrders.partnerOrderRequests')->get();
    foreach ($orders as $order) {
        dd($order);
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
    (new App\Http\Route\Prefix\V3\Route())->set($api);
});
