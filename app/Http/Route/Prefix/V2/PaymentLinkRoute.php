<?php namespace App\Http\Route\Prefix\V2;
class PaymentLinkRoute
{
    public function set($api)
    {
        $api->group(['prefix'     => 'payment-links',
                     'middleware' => ['paymentLink.auth']
        ], function ($api) {
            $api->get('/', 'PaymentLink\PaymentLinkController@index');
            $api->post('/', 'PaymentLink\PaymentLinkController@store');
            $api->post('/due-collection', 'PaymentLink\PaymentLinkController@createPaymentLinkForDueCollection');
            $api->post('/{link}', 'PaymentLink\PaymentLinkController@statusChange');
            $api->get('/default', 'PaymentLink\PaymentLinkController@getDefaultLink');
            $api->get('/{link}/payments', 'PaymentLink\PaymentLinkController@getPaymentLinkPayments');
            $api->get('/{link}/payments/{payment}', 'PaymentLink\PaymentLinkController@paymentLinkPaymentDetails');
        });
        $api->group(['prefix' => 'payment-links'], function ($api) {
            $api->get('/{link}', 'PaymentLink\PaymentLinkController@show');
            $api->get('/bills/clear', 'PaymentLink\PaymentLinkBillController@clearBill');
        });
    }
}
