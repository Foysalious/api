<?php

namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class QRPaymentRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners', 'middleware' => ['accessToken']], function ($api) {
            $api->group(['prefix' => 'qr-payments'], function ($api) {
                $api->get('/gateways', 'QRPayableGenerator\\GatewayController@index');
                $api->post('/generate-qr', 'QRPayableGenerator\\QRPaymentController@generateQR');
            });
        });

        $api->group(['prefix' => 'qr-payments'], function ($api) {
            $api->post('/validate/{payment_method}', 'QRPayableGenerator\\QRPaymentController@validatePayment');
        });
    }
}