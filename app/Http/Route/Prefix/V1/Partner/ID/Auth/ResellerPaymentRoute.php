<?php namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class ResellerPaymentRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners', 'middleware' => ['paymentLink.auth']], function ($api) {
            $api->group(['prefix' => 'reseller-payment'], function ($api) {
                $api->get('/store-configuration', 'ResellerPayment\\StoreConfigurationController@get');
                $api->post('/store-configuration', 'ResellerPayment\\StoreConfigurationController@store');
                $api->get('/payment-gateways', 'ResellerPayment\\PaymentServiceController@getPaymentGateway');
                $api->get('/payment-service-charge', 'ResellerPayment\\PaymentServiceController@getPaymentServiceCharge');
                $api->post('payment-service-charge', 'ResellerPayment\\PaymentServiceController@storePaymentServiceCharge');

                $api->get('/emi-info/manager', 'ResellerPayment\\PaymentServiceController@emiInformationForManager');
            });
            $api->group(["prefix" => 'merchant-on-boarding'], function ($api) {
                $api->get('/category', "ResellerPayment\\MEF\\MerchantEnrollmentController@getCategoryWiseDetails");
            });
        });
    }
}