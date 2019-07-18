<?php namespace App\Http\Route\Prefix\V2;

class PaymentLinkRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'payment-links', 'middleware' => ['jwtGlobalAuth']], function ($api) {
            $api->post('/bills/clear', 'PaymentLink\PaymentLinkBillClearController@clearBill');
        });
        $api->group(['prefix' => 'payment-links', 'middleware' => ['paymentLink.auth']], function ($api) {
            $api->get('/', 'PaymentLink\PaymentLinkController@index');
            $api->post('/', 'PaymentLink\PaymentLinkController@store');
            $api->post('/{link}', 'PaymentLink\PaymentLinkController@statusChange');
            $api->get('/default', 'PaymentLink\PaymentLinkController@getDefaultLink');
            $api->get('/{link}/payments', 'PaymentLink\PaymentLinkController@getPaymentLinkPayments');
            $api->get('/{link}/payments/{payment}', 'PaymentLink\PaymentLinkController@paymentLinkPaymentDetails');
        });
    }
}
