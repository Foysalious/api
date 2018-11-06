<?php namespace App\Http\Route\Prefix\V2;

class PartnerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners'], function ($api) {
            $api->group(['prefix' => '{partner}'], function ($api) {
                $api->get('/', 'PartnerController@show');
                $api->get('locations', 'PartnerController@getLocations');
                $api->get('categories', 'PartnerController@getCategories');
                $api->get('categories/{category}/services', 'PartnerController@getServices');
            });
            $api->get('rewards/faqs', 'Partner\PartnerRewardController@getFaqs');
        });
        $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
            $api->group(['prefix' => 'e-shop'], function ($api) {
                $api->group(['prefix' => 'order'], function ($api) {
                    $api->get('/', 'EShopOrderController@index');
                    $api->get('/{order}', 'EShopOrderController@show');
                });
            });
            $api->get('operations', 'Partner\OperationController@index');
            $api->post('operations', 'Partner\OperationController@store');
            $api->post('register', 'CustomerController@store');
            $api->post('categories', 'Partner\OperationController@saveCategories');
            $api->post('top-up', 'TopUpController@topUp');
            $api->get('search', 'SearchController@search');
            $api->group(['prefix' => 'subscriptions'], function ($api) {
                $api->get('/', 'Partner\PartnerSubscriptionController@index');
                $api->post('/', 'Partner\PartnerSubscriptionController@store');
                $api->post('/upgrade', 'Partner\PartnerSubscriptionController@update');
            });
            $api->group(['prefix' => 'resources'], function ($api) {
                $api->post('/', 'Resource\PersonalInformationController@store');
                $api->group(['prefix' => '{resource}', 'middleware' => ['partner_resource.auth']], function ($api) {
                    $api->get('/', 'Resource\PersonalInformationController@index');
                    $api->post('/', 'Resource\PersonalInformationController@update');
                });
            });
            $api->get('completion', 'Partner\ProfileCompletionController@getProfileCompletion');
            $api->get('collections', 'PartnerOrderPaymentController@index');
            $api->get('training', 'PartnerTrainingController@redirect');
            $api->post('pay-sheba', 'PartnerTransactionController@payToSheba');
            $api->group(['prefix' => 'orders'], function ($api) {
                $api->group(['prefix' => '{order}', 'middleware' => ['partner_order.auth']], function ($api) {
                    $api->get('/', 'PartnerOrderController@showV2');
                    $api->get('bills', 'PartnerOrderController@getBillsV2');
                    $api->post('services', 'PartnerOrderController@addService');
                    $api->post('collect', 'PartnerOrderController@collectMoney');
                });
            });
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->group(['prefix' => '{job}', 'middleware' => ['partner_job.auth']], function ($api) {
                    $api->put('/', 'PartnerJobController@update');

                    $api->group(['prefix' => 'materials'], function ($api) {
                        $api->get('/', 'PartnerJobController@getMaterials');
                    });
                    $api->group(['prefix' => 'cancel-requests'], function ($api) {
                        $api->post('/', 'PartnerCancelRequestController@store');
                        $api->get('reasons', 'PartnerCancelRequestController@cancelReasons');
                    });
                });
                $api->get('/cancel-request', 'PartnerJobController@cancelRequests');
            });
            $api->group(['prefix' => 'job_service/{job_service}'], function ($api) {
                $api->post('/update', 'JobServiceController@update');
                $api->delete('/', 'JobServiceController@destroy');
            });
            $api->group(['prefix' => 'complains'], function ($api) {
                $api->get('/', 'ComplainController@index');
                $api->post('/', 'ComplainController@storeForPartner');
                $api->get('/list', 'ComplainController@complainList');
                $api->get('/resolved-category', 'ComplainController@resolvedCategory');
                $api->group(['prefix' => '{complain}'], function ($api) {
                    $api->post('/', 'ComplainController@postPartnerComment');
                    $api->get('/', 'ComplainController@showPartnerComplain');
                    $api->post('/status', 'ComplainController@updateStatus');
                });
            });
            $api->group(['prefix' => 'rewards'], function ($api) {
                $api->get('/', 'Partner\PartnerRewardController@index');
                $api->get('/history', 'Partner\PartnerRewardController@history');
                $api->group(['prefix' => 'shop'], function ($api) {
                    $api->get('/', 'Partner\PartnerRewardShopController@index');
                    $api->get('/history', 'Partner\PartnerRewardShopController@history');
                    $api->post('/purchase', 'Partner\PartnerRewardShopController@purchase');
                    $api->get('/purchasable', 'Partner\PartnerRewardShopController@purchasable');
                });
                $api->get('/{reward}', 'Partner\PartnerRewardController@show');
            });
            $api->group(['prefix' => 'notifications'], function ($api) {
                $api->put('/', 'Partner\PartnerNotificationController@update');
            });
            $api->get('get-profile', 'ResourceController@getResourceData');
            $api->get('settings', 'Partner\OperationController@isOnPremiseAvailable');
            $api->get('my-customer-info','Partner\AsCustomerController@getResourceCustomerProfile');
            $api->group(['prefix' => 'partner-wallet'], function ($api) {
                $api->post('purchase', 'PartnerWalletController@purchase');
                $api->post('validate', 'PartnerWalletController@validatePayment');
            });
        });
    }
}