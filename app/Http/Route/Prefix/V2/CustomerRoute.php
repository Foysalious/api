<?php namespace App\Http\Route\Prefix\V2;

class CustomerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'customers'], function ($api) {
            $api->group(['prefix' => '{customer}', 'middleware' => ['customer.auth']], function ($api) {
                $api->get('partners-nearby', 'PartnerLocationController@getNearbyPartners');
                $api->get('checkout-info', 'CustomerController@getDeliveryInfo');
                $api->post('purchase-gift-card', 'GiftCardController@purchaseGiftCard');
                $api->group(['prefix' => 'settings'], function ($api) {
                    $api->get('/', 'Settings\SettingsController@getCustomerSettings');
                    $api->post('/rating', 'Settings\SettingsController@giveRating');
                    $api->get('review', 'Settings\SettingsController@getCustomerReviewSettings');
                    $api->get('payment', 'Settings\SettingsController@addPayment');
                });
                $api->group(['prefix' => 'info-call'], function ($api) {
                    $api->get('/', 'InfoCallController@index');
                    $api->get('/details/{id}', 'InfoCallController@getDetails');
                    $api->post('/', 'InfoCallController@store');
                });
                $api->group(['prefix' => 'notifications'], function ($api) {
                    $api->put('/', 'CustomerNotificationController@update');
                    $api->get('/', 'CustomerNotificationController@index');
                });
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
                    $api->post('promotions', 'PromotionV3Controller@autoApplyPromotion');
                    $api->post('promotions/add', 'PromotionController@addPromotion');
                    $api->get('promotions/applicable', 'PromotionController@getAllApplicable');
                    $api->group(['prefix' => '{order}'], function ($api) {
                        $api->get('/', 'CustomerOrderController@show');
                    });
                });
                $api->group(['prefix' => 'subscriptions'], function ($api) {
                    $api->post('/', 'Subscription\CustomerSubscriptionController@placeSubscriptionRequest');
                    $api->get('{subscription}/payment', 'Subscription\CustomerSubscriptionController@clearPayment');
                    $api->get('order-lists', 'Subscription\CustomerSubscriptionController@index');
                    $api->get('{subscription}/details', 'Subscription\CustomerSubscriptionController@show');
                    $api->get('{subscription}/check-renewal-status', 'Subscription\CustomerSubscriptionController@checkRenewalStatus');
                });
                $api->group(['prefix' => 'movie-ticket'], function ($api) {
                    $api->get('movie-list', 'MovieTicketController@getAvailableTickets');
                    $api->get('theatre-list', 'MovieTicketController@getAvailableTheatres');
                    $api->get('theatre-seat-status', 'MovieTicketController@getTheatreSeatStatus');
                    $api->get('history', 'MovieTicketController@history');
                    $api->get('history/{history_id}', 'MovieTicketController@historyDetails');
                    $api->get('promotions', 'MovieTicketController@getPromotions');
                    $api->post('promotions/add', 'MovieTicketController@applyPromo');
                    $api->post('book-tickets', 'MovieTicketController@bookTickets');
                    $api->post('update-status', 'CustomerMovieTicketController@updateTicketStatus');
                });

                (new TransportRoute())->set($api);

                $api->group(['prefix' => 'jobs'], function ($api) {
                    $api->get('/', 'JobController@index');
                    $api->get('cancel-reason', 'JobController@cancelReason');
                    $api->group(['prefix' => '{job}', 'middleware' => ['customer_job.auth']], function ($api) {
                        $api->get('/', 'JobController@show');
                        $api->get('bills', 'JobController@getBills');
                        $api->get('invoice', 'JobController@getInvoice');
                        $api->get('bills/clear', 'JobController@clearBills')->middleware('concurrent_request:customer');
                        $api->post('reschedule', 'JobController@rescheduleJob');
                        $api->get('logs', 'JobController@getLogs');
                        $api->get('logs/order', 'JobController@getOrderLogs');
                        $api->post('reviews', 'ReviewController@store');
                        $api->post('promotions', 'Customer\CustomerJobController@addPromotion');
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
                $api->group(['prefix' => 'due-orders'], function ($api) {
                    $api->get('/', 'CustomerOrderController@dueOrders');
                });
            });
        });
    }
}
