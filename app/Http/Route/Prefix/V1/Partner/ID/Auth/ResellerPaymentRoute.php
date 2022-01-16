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
                $api->get('/banner-and-status', 'ResellerPayment\\PaymentServiceController@bannerAndStatus');
                $api->get('/pgw-details', 'ResellerPayment\\PaymentServiceController@getPaymentGatewayDetails');
            });
            $api->group(["prefix" => 'merchant-on-boarding'], function ($api) {
                $api->get('/category-list', "ResellerPayment\\MEF\\MerchantEnrollmentController@categoryListWithCompletion");
                $api->get('/category', "ResellerPayment\\MEF\\MerchantEnrollmentController@getCategoryWiseDetails");
                $api->post('/category', "ResellerPayment\\MEF\\MerchantEnrollmentController@postCategoryWiseDetails");
                $api->post('/document-upload', "ResellerPayment\\MEF\\MerchantEnrollmentController@uploadCategoryWiseDocument");
                $api->get('/required-document-list', "ResellerPayment\\MEF\\MerchantEnrollmentController@requiredDocuments");
                $api->post('/apply', "ResellerPayment\\MEF\\MerchantEnrollmentController@apply");
            });
            $api->group(["prefix" => 'survey'], function ($api) {
                $api->get('/', "Partner\\SurveyController@getQuestions");
                $api->post('/', "Partner\\SurveyController@storeResult");
            });
        });
    }
}