<?php namespace App\Http\Route\Prefix\V2;

class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'v2', 'namespace' => 'App\Http\Controllers'], function ($api) {
            $api->get('validate-location','LocationController@validateLocation');
            $api->get('partners', 'PartnerLocationController@getPartners');
            $api->post('subscription', 'PushSubscriptionController@store');
            $api->get('car-rental-info', 'ShebaController@sendCarRentalInfo');
            $api->get('payments', 'ShebaController@getPayments');
            $api->get('butcher-info', 'ShebaController@sendButcherInfo');
            $api->post('service-requests', 'ServiceRequestController@store');
            $api->post('transactions/{transactionID}', 'ShebaController@checkTransactionStatus');
            $api->post('password/email', 'Auth\PasswordController@sendResetPasswordEmail');
            $api->post('password/validate', 'Auth\PasswordController@validatePasswordResetCode');
            $api->post('password/reset', 'Auth\PasswordController@reset');
            $api->post('events', 'EventController@store');
            $api->get('top-up/fail/ssl', 'TopUpController@sslFail');
            $api->group(['prefix' => 'wallet'], function ($api) {
                $api->post('recharge', 'WalletController@recharge');
                $api->post('purchase', 'WalletController@purchase');
                $api->post('validate', 'WalletController@validatePayment');
                $api->get('faqs', 'WalletController@getFaqs');
            });

            $api->group(['prefix' => 'faqs'], function ($api) {
                $api->get('order', 'JobController@getFaqs');
            });

            $api->group(['prefix' => 'ssl'], function ($api) {
                $api->post('validate', 'SslController@validatePayment');
            });

            $api->group(['prefix' => 'bkash'], function ($api) {
                $api->post('validate', 'BkashController@validatePayment');
                $api->get('paymentID/{paymentID}', 'BkashController@getPaymentInfo');
            });
            $api->group(['prefix' => 'orders'], function ($api) {
                $api->get('online', 'OrderController@clearPayment');
                $api->group(['prefix' => 'payments'], function ($api) {
                    $api->post('success', 'SslController@validatePayment');
                    $api->post('fail', 'SslController@validatePayment');
                    $api->post('cancel', 'SslController@validatePayment');
                });
            });
            $api->group(['prefix' => 'payments'], function ($api) {
                $api->group(['prefix' => 'cbl'], function ($api) {
                    $api->post('success', 'CblController@validateCblPGR');
                    $api->post('fail', 'CblController@validateCblPGR');
                    $api->post('cancel', 'CblController@validateCblPGR');
                });
            });
            $api->group(['prefix' => 'login'], function ($api) {
                $api->post('gmail', 'Auth\GoogleController@login');
            });
            $api->group(['prefix' => 'register'], function ($api) {
                $api->post('gmail', 'Auth\GoogleController@register');
                $api->post('kit/partner', 'Auth\PartnerRegistrationController@register');
            });
            $api->get('times', 'ScheduleTimeController@index');
            $api->get('settings', 'HomePageSettingController@index');
            $api->get('settings/top-up', 'TopUpController@getVendor');
            $api->get('settings/car', 'HomePageSettingController@getCar');
            $api->get('home-grids', 'HomeGridController@index');
            $api->group(['prefix' => 'category-groups'], function ($api) {
                $api->get('', 'CategoryGroupController@index');
                $api->group(['prefix' => '{id}'], function ($api) {
                    $api->get('', 'CategoryGroupController@show');
                });
            });
            $api->group(['prefix' => 'categories'], function ($api) {
                $api->group(['prefix' => '{id}'], function ($api) {
                    $api->get('', 'CategoryController@show');
                    $api->get('services', 'CategoryController@getServices');
                    $api->get('reviews', 'CategoryController@getReviews');
                    $api->get('locations/{location}/partners', 'CategoryController@getPartnersOfLocation');
                });
            });
            $api->group(['prefix' => 'services'], function ($api) {
                $api->get('', 'ServiceController@index');
            });
            $api->group(['prefix' => 'locations'], function ($api) {
                $api->get('/', 'LocationController@index');
                $api->get('{location}/partners', 'PartnerController@findPartners');
                $api->get('current', 'LocationController@getCurrent');
            });
            (new CustomerRoute())->set($api);
            (new AffiliateRoute())->set($api);
            (new PartnerRoute())->set($api);
            $api->group(['prefix' => 'resources/{resource}', 'middleware' => ['resource.auth']], function ($api) {
                $api->group(['prefix' => 'jobs'], function ($api) {
                    $api->group(['prefix' => '{job}', 'middleware' => ['resource_job.auth']], function ($api) {
                        $api->get('bills', 'ResourceJobController@getBills');
                        $api->post('extends', 'ResourceScheduleController@extendTime');
                        $api->post('reviews', 'ResourceJobRateController@store');
                        $api->group(['prefix' => 'rates'], function ($api) {
                            $api->get('/', 'ResourceJobRateController@index');
                            $api->post('/', 'RateController@storeCustomerReview');
                        });
                    });
                });
            });
            $api->get('updates', 'UpdateController@getUpdates');
        });
        return $api;
    }
}