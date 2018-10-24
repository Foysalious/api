<?php

use Sheba\Payment\Adapters\Payable\OrderAdapter;
use Sheba\Payment\ShebaPayment;

Route::get('/', function () {

    $order = \App\Models\Order::find(62063);
    $payment_method = 'partner_wallet';
    $order_adapter = new OrderAdapter($order->partnerOrders[0], 1);
    $payment = (new ShebaPayment($payment_method))->init($order_adapter->getPayable());

    $a = $payment->isInitiated() ? $payment : null;
    dd($a);


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
