<?php namespace App\Http\Route\Prefix\V3;
class PaymentLinkRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'payment-links',
            'middleware' => ['paymentLink.auth']
        ], function ($api) {
            $api->get('/transactions', 'PaymentLink\PaymentLinkController@transactionListV3');
            $api->get('/{link}/payments/{payment}', 'PaymentLink\PaymentLinkController@paymentLinkPaymentDetails');
        });
    }
}
