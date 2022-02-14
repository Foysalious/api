<?php

namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class QRPaymentRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners', 'middleware' => ['paymentLink.auth']], function ($api) {
            $api->group(['prefix' => 'qr-payment'], function ($api) {
                $api->get('/gateway', 'QRPayment\\GatewayController@index');
            });
        });
    }
}