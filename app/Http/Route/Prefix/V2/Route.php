<?php

namespace App\Http\Route\Prefix\V2;


class Route
{
    public function set($api)
    {
        $api->group(['prefix' => 'v2', 'namespace' => 'App\Http\Controllers'], function ($api) {
            $api->post('subscription', 'PushSubscriptionController@store');
            $api->get('car-rental-info', 'ShebaController@sendCarRentalInfo');
            $api->get('butcher-info', 'ShebaController@sendButcherInfo');
            $api->post('service-requests', 'ServiceRequestController@store');
            $api->post('transactions/{transactionID}', 'ShebaController@checkTransactionStatus');
            $api->post('password/email', 'Auth\PasswordController@sendResetPasswordEmail');
            $api->post('password/validate', 'Auth\PasswordController@validatePasswordResetCode');
            $api->post('password/reset', 'Auth\PasswordController@reset');
            $api->post('events', 'EventController@store');
            $api->group(['prefix' => 'wallet'], function ($api) {
                $api->post('recharge', 'WalletController@recharge');
                $api->post('purchase', 'WalletController@purchase');
                $api->post('validate', 'WalletController@validatePaycharge');
                $api->get('faqs', 'WalletController@getFaqs');
            });
            $api->group(['prefix' => 'faqs'], function ($api) {
                $api->get('order', 'JobController@getFaqs');
            });

            $api->group(['prefix' => 'ssl'], function ($api) {
                $api->post('validate', 'SslController@validatePaycharge');
            });

            $api->group(['prefix' => 'bkash'], function ($api) {
                $api->post('validate', 'BkashController@validatePaycharge');
                $api->get('paymentID/{paymentID}', 'BkashController@getPaymentInfo');
            });
            $api->group(['prefix' => 'orders'], function ($api) {
                $api->get('online', 'OrderController@clearPayment');
                $api->group(['prefix' => 'payments'], function ($api) {
                    $api->post('success', 'SslController@validatePaycharge');
                    $api->post('fail', 'SslController@validatePaycharge');
                    $api->post('cancel', 'SslController@validatePaycharge');
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
            $api->group(['prefix' => 'locations'], function ($api) {
                $api->get('{location}/partners', 'PartnerController@findPartners');
                $api->get('current', 'LocationController@getCurrent');
            });
            $api->group(['prefix' => 'partners'], function ($api) {
                $api->group(['prefix' => '{partner}'], function ($api) {
                    $api->get('/', 'PartnerController@show');
                    $api->get('locations', 'PartnerController@getLocations');
                    $api->get('categories', 'PartnerController@getCategories');
                    $api->get('categories/{category}/services', 'PartnerController@getServices');
                });
                $api->get('rewards/faqs', 'Partner\PartnerRewardController@getFaqs');
            });
            (new CustomerRoute())->set($api);
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
            $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
                $api->get('operations', 'Partner\OperationController@index');
                $api->post('operations', 'Partner\OperationController@store');
                $api->post('register', 'CustomerController@store');
                $api->post('categories', 'Partner\OperationController@saveCategories');
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
                $api->get('get-profile', 'ResourceController@getResourceData');
            });
            $api->group(['prefix' => 'affiliates/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
                $api->get('dashboard', 'AffiliateController@getDashboardInfo');
                $api->get('partner-affiliates', 'PartnerAffiliationController@index');
                $api->post('partner-affiliates', 'PartnerAffiliationController@store');
                $api->post('top-up', 'TopUpController@topUp');
            });
            $api->get('updates', 'UpdateController@getUpdates');

        });
        return $api;
    }
}