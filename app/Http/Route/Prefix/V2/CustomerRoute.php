<?php namespace App\Http\Route\Prefix\V2;

class CustomerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'customers'], function ($api) {
            $api->group(['prefix' => '{customer}', 'middleware' => ['customer.auth']], function ($api) {
                $api->get('checkout-info', 'CustomerController@getDeliveryInfo');
                $api->get('settings/review', 'Settings\SettingsController@getCustomerReviewSettings');
                $api->get('settings', 'Settings\SettingsController@getCustomerSettings');
                $api->put('notifications', 'CustomerNotificationController@update');
                $api->post('top-up', 'TopUpController@topUp');
                $api->group(['prefix' => 'bkash'], function ($api) {
                    $api->post('create', 'BkashController@create')->middleware('customer_job.auth');
                    $api->post('execute', 'BkashController@execute');
                });
                $api->group(['prefix' => 'favorites'], function ($api) {
                    $api->get('/', 'CustomerFavoriteController@index');
                    $api->post('/', 'CustomerFavoriteController@store');
                    $api->put('/', 'CustomerFavoriteController@update');
                    $api->delete('{favorite}', 'CustomerFavoriteController@destroy');
                });
                $api->group(['prefix' => 'promotions'], function ($api) {
                    $api->get('/', 'PromotionController@index');
                    $api->post('/', 'PromotionController@addPromo');
                    $api->get('applicable', 'PromotionController@getApplicablePromotions');
                });

                $api->group(['prefix' => 'delivery-addresses'], function ($api) {
                    $api->get('/', 'CustomerDeliveryAddressController@index');
                    $api->get('filter', 'CustomerDeliveryAddressController@filterAddress');
                    $api->post('/', 'CustomerDeliveryAddressController@store');
                    $api->post('{delivery_address}', 'CustomerDeliveryAddressController@update');
                    $api->delete('{delivery_address}', 'CustomerDeliveryAddressController@destroy');
                });
                $api->group(['prefix' => 'orders'], function ($api) {
                    $api->post('/', 'OrderController@store');
                    $api->get('/', 'CustomerOrderController@index');
                    $api->get('valid', 'OrderController@checkOrderValidity');
                    $api->get('payment/valid', 'OrderController@checkInvoiceValidity');
                    $api->post('promotions', 'PromotionController@autoApplyPromotion');
                    $api->post('promotions/add', 'PromotionController@addPromotion');
                    $api->get('promotions/applicable', 'PromotionController@getAllApplicable');
                    $api->group(['prefix' => '{order}'], function ($api) {
                        $api->get('/', 'CustomerOrderController@show');
                    });
                });
                $api->group(['prefix' => 'subscriptions'], function ($api) {
                    $api->post('/', 'Subscription\CustomerSubscriptionController@placeSubscriptionRequest');
                    $api->get('{subscription}/payment', 'Subscription\CustomerSubscriptionController@clearPayment');
                    $api->get('{subscription}/orders', 'Subscription\CustomerSubscriptionController@getSubscriptionOrders');
                });
                $api->group(['prefix' => 'jobs'], function ($api) {
                    $api->get('/', 'JobController@index');
                    $api->get('cancel-reason', 'JobController@cancelReason');
                    $api->group(['prefix' => '{job}', 'middleware' => ['customer_job.auth']], function ($api) {
                        $api->get('/', 'JobController@show');
                        $api->get('bills', 'JobController@getBills');
                        $api->get('bills/clear', 'JobController@clearBills');
                        $api->get('logs', 'JobController@getLogs');
                        $api->get('logs/order', 'JobController@getOrderLogs');
                        $api->post('reviews', 'ReviewController@store');
                        $api->group(['prefix' => 'complains'], function ($api) {
                            $api->get('/', 'ComplainController@index');
                            $api->post('/', 'ComplainController@storeForCustomer');
                            $api->group(['prefix' => '{complain}'], function ($api) {
                                $api->post('/', 'ComplainController@postCustomerComment');
                                $api->get('/', 'ComplainController@showCustomerComplain');
                            });
                        });
                        $api->group(['prefix' => 'rates'], function ($api) {
                            $api->get('/', 'RateController@index');
                            $api->post('/', 'RateController@store');
                        });
                        $api->post('cancel', 'JobController@cancel');
                    });
                });
                $api->group(['prefix' => 'transactions'], function ($api) {
                    $api->get('/', 'Customer\CustomerTransactionController@index');
                });

            });
        });
    }
}