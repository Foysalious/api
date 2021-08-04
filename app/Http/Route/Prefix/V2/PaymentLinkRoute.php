<?php namespace App\Http\Route\Prefix\V2;
class PaymentLinkRoute
{
    public function set($api)
    {
        $api->group(['prefix'     => 'payment-links',
                     'middleware' => ['paymentLink.auth']
        ], function ($api) {
            $api->get('/', 'PaymentLink\PaymentLinkController@index');
            $api->get('/partner-payment-links', 'PaymentLink\PaymentLinkController@partnerPaymentLinks');
            $api->post('/', 'PaymentLink\PaymentLinkController@store')->middleware(['partner.status']);
            $api->post('/due-collection', 'PaymentLink\PaymentLinkController@createPaymentLinkForDueCollection');
            $api->post('/{link}', 'PaymentLink\PaymentLinkController@statusChange');
            $api->get('/default', 'PaymentLink\PaymentLinkController@getDefaultLink');
            $api->get('/dashboard', 'PaymentLink\PaymentLinkController@getDashboard');
            $api->get('/custom-link-data', 'PaymentLink\PaymentLinkCreateController@customLinkCreateData');
            $api->get('/subscription-wise-gateway-charges', 'PaymentLink\PaymentLinkCreateController@subscriptionWiseCharges');
            $api->get('/{link}/payments', 'PaymentLink\PaymentLinkController@getPaymentLinkPayments');
            $api->get('/{link}/payments/{payment}', 'PaymentLink\PaymentLinkController@paymentLinkPaymentDetails');
            $api->get('/transactions', 'PaymentLink\PaymentLinkController@transactionList');
        });
        $api->group(['prefix' => 'payment-links'], function ($api) {
            $api->get('/{link}', 'PaymentLink\PaymentLinkController@show');
            $api->get('/bills/clear', 'PaymentLink\PaymentLinkBillController@clearBill');
        });
    }
}
