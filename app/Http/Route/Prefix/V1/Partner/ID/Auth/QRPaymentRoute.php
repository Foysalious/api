<?php

namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class QRPaymentRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners', 'middleware' => ['paymentLink.auth']], function ($api) {
            $api->group(['prefix' => 'qr-payments'], function ($api) {
                $api->get('/gateways', 'QRPayment\\GatewayController@index');
                $api->post('/generate-qr', 'QRPayment\\QRPaymentController@generateQR');
            });
        });

        $api->group(['prefix' => 'qr-payments'], function ($api) {
            $api->post('/validate/{payment_method}', 'QRPayment\\QRPaymentController@validatePayment');
        });
    }
}