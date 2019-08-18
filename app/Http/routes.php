<?php

use Sheba\PushNotificationHandler;
use Sheba\Repositories\Interfaces\PaymentLinkRepositoryInterface;

Route::get('/', function () {
    $payment = \App\Models\Payment::find(12265);
    $paymentLinkRepository = app(PaymentLinkRepositoryInterface::class);
    $payment_link = $paymentLinkRepository->getPaymentLinkByLinkId($payment->payable->type_id);
    $partner = $payment_link->getPaymentReceiver();
    $topic = config('sheba.push_notification_topic_name.manager') . $partner->id;
    $channel = config('sheba.push_notification_channel_name.manager');
    $sound = config('sheba.push_notification_sound.manager');
    $formatted_amount = number_format($payment_link->getAmount(), 2);
    (new PushNotificationHandler())->send([
        "title" => 'Order Successful',
        "message" => "$formatted_amount Tk has been collected from {$payment_link->getPayer()->name} by order link- {$payment_link->getLinkID()}",
        "event_type" => 'PosOrder',
        "event_id" => $payment_link->getTarget()->id,
        "sound" => "notification_sound",
        "channel_id" => $channel
    ], $topic, $channel, $sound);
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
