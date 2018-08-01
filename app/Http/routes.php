<?php


Route::get('/', function () {
    return ['code' => 200, 'message' => "Success. This project will hold the api's"];
});
$api = app('Dingo\Api\Routing\Router');

/*
|--------------------------------------------------------------------------
| Version Reminder
|--------------------------------------------------------------------------
|
| When next version comes add a prefix to the old version
| routes and change API_PREFIX in api.php file to null
|
|
*/
$api->version('v1', function ($api) {
    $api->group(['prefix' => 'v1', 'namespace' => 'App\Http\Controllers'], function ($api) {
        $api->post('login', 'Auth\LoginController@login');
        $api->post('register', 'Auth\RegistrationController@register');
        $api->group(['prefix' => 'login'], function ($api) {
            $api->post('facebook', 'FacebookController@login');
        });
        $api->group(['prefix' => 'register'], function ($api) {
            $api->post('email', 'Auth\RegistrationController@registerByEmailAndMobile');
            $api->post('facebook', 'FacebookController@register');
        });
        $api->post('continue-with-kit', 'FacebookController@continueWithKit');
        $api->post('continue-with-facebook', 'FacebookController@continueWithFacebook');
        $api->get('authenticate', 'AccountController@checkForAuthentication');
        $api->post('account', 'AccountController@encryptData');
        $api->get('decrypt', 'AccountController@decryptData');
        $api->get('info', 'ShebaController@getInfo');
        $api->get('versions', 'ShebaController@getVersions');
        $api->get('images', 'ShebaController@getImages');
        $api->get('sliders', 'SliderController@index');
        $api->get('locations', 'LocationController@getAllLocations');
        $api->get('lead-reward', 'ShebaController@getLeadRewardAmount');
        $api->get('search', 'SearchController@searchService');
        $api->get('career', 'CareerController@getVacantPosts');
        $api->post('career', 'CareerController@apply');
        $api->get('category-service', 'CategoryServiceController@getCategoryServices');
        $api->get('job-times', 'JobController@getPreferredTimes');
        $api->get('times', 'JobController@getPreferredTimes');
        $api->get('cancel-job-reasons', 'JobController@cancelJobReasons');

        $api->post('voucher-valid', 'CheckoutController@validateVoucher');
        $api->post('vouchers', 'CheckoutController@validateVoucher');

        $api->post('rating', 'ReviewController@giveRatingFromEmail');
        $api->post('sms', 'SmsController@send');
        $api->post('faq', 'ShebaController@sendFaq');
        $api->group(['prefix' => 'offers'], function ($api) {
            $api->get('/', 'OfferController@index');
            $api->get('{offer}', 'OfferController@show');
        });
        $api->get('offer/{offer}/similar', 'ShebaController@getSimilarOffer');

        $api->group(['prefix' => 'navigation'], function ($api) {
            $api->get('/', 'NavigationController@getNavList');
        });
        $api->group(['prefix' => 'jobs'], function ($api) {
            $api->get('times', 'JobController@getPreferredTimes');
        });
        $api->group(['prefix' => 'categories'], function ($api) {
            $api->get('/', 'CategoryController@index');
            $api->get('{category}/secondaries', 'CategoryController@getSecondaries');
            $api->get('{category}/secondaries/services', 'CategoryController@getSecondaryServices');
            $api->get('{category}/services', 'CategoryController@getServices');
            $api->get('{category}/master', 'CategoryController@getMaster');
        });
        $api->group(['prefix' => 'service'], function ($api) {
            $api->get('{service}/get-prices', 'ServiceController@getPrices');
            $api->get('{service}/location/{location}/partners', 'ServiceController@getPartners');
            $api->post('{service}/location/{location}/partners', 'ServiceController@getPartners');
            $api->post('{service}/{location}/change-partner', 'ServiceController@changePartner');
            $api->get('/{service}/reviews', 'ServiceController@getReviews');
            //For Back-end
            $api->post('{service}/change-partner', 'ServiceController@changePartnerWithoutLocation');
        });
        $api->group(['prefix' => 'services'], function ($api) {
            $api->get('/', 'ServiceController@index');
            $api->get('{service}', 'ServiceController@get');
            $api->get('{service}/valid', 'ServiceController@checkForValidity');
            $api->get('{service}/similar', 'ServiceController@getSimilarServices');
            $api->get('{service}/reviews', 'ServiceController@getReviews');
            $api->get('{service}/locations/{location}/partners', 'ServiceController@getPartnersOfLocation');
            $api->post('{service}/locations/{location}/partners', 'ServiceController@getPartners');
        });
        $api->group(['prefix' => 'partner'], function ($api) {
            $api->get('/', 'PartnerController@index');
            $api->get('{partner}/services', 'PartnerController@getPartnerServices');
            $api->get('{partner}/reviews', 'PartnerController@getReviews');
        });
        $api->group(['prefix' => 'customer', 'middleware' => ['customer.auth']], function ($api) {
            $api->get('{customer}', 'CustomerController@getCustomerInfo');
            $api->post('{customer}/edit', 'CustomerController@editInfo');
            $api->get('{customer}/general-info', 'CustomerController@getCustomerGeneralInfo');
            $api->get('{customer}/intercom-info', 'CustomerController@getIntercomInfo');
            $api->get('{customer}/checkout-info', 'CustomerController@getDeliveryInfo');
            $api->get('{customer}/order-list', 'OrderController@getNotClosedOrderInfo');
            $api->get('{customer}/order-history', 'OrderController@getClosedOrderInfo');
            $api->get('{customer}/cancel-order-list', 'OrderController@getCancelledOrders');
            $api->get('{customer}/referral', 'CustomerController@getReferral');
            $api->post('{customer}/send-referral-request-email', 'CustomerController@sendReferralRequestEmail');
            $api->get('{customer}/promo', 'PromotionController@getPromo');
            $api->post('{customer}/promo', 'PromotionController@addPromo');
            $api->post('{customer}/suggest-promo', 'PromotionController@suggestPromo');

            $api->post('{customer}/sp-payment', 'CheckoutController@spPayment');
            $api->post('{customer}/order-valid', 'OrderController@checkOrderValidity');
            $api->post('{customer}/modify-review', 'ReviewController@modifyReview');
            $api->get('{customer}/job/{job}', 'JobController@getInfo');
            $api->post('{customer}/{job}/cancel', 'JobController@cancelJob');

            $api->post('{customer}/ask-quotation', 'CustomOrderController@askForQuotation');
            $api->get('{customer}/custom-order', 'CustomOrderController@getCustomOrders');
            $api->get('{customer}/custom-order/{custom_order}/quotation', 'CustomOrderController@getCustomOrderQuotation');
            $api->get('{customer}/custom-order/{custom_order}/discussion', 'CustomOrderController@getCommentForDiscussion');
            $api->post('{customer}/custom-order/{custom_order}/discussion', 'CustomOrderController@postCommentOnDiscussion');

//            $api->post('{customer}/checkout/place-order', 'CheckoutController@placeOrder');
//            $api->post('{customer}/checkout/place-order-with-online-payment', 'CheckoutController@placeOrderWithPayment');
        });
        $api->group(['prefix' => 'customers/{customer}', 'middleware' => ['customer.auth']], function ($api) {
            $api->get('/', 'CustomerController@index');
            $api->group(['prefix' => 'edit'], function ($api) {
                $api->put('/', 'CustomerController@update');
                $api->put('email', 'CustomerController@updateEmail');
                $api->put('password', 'CustomerController@updatePassword');
                $api->post('picture', 'CustomerController@updatePicture');
                $api->put('mobile', 'CustomerController@updateMobile');
            });
            $api->post('reviews', 'ReviewController@modifyReview');
            $api->get('notifications', 'CustomerController@getNotifications');
            $api->post('suggest-promo', 'PromotionController@suggestPromo');
            $api->put('addresses/{address}', 'CustomerAddressController@update');
        });
        $api->group(['prefix' => 'checkout'], function ($api) {
            $api->get('place-order-final', 'CheckoutController@placeOrderFinal');
            $api->get('sp-payment-final', 'CheckoutController@spPaymentFinal');
        });
        $api->group(['prefix' => 'business'], function ($api) {
            $api->get('check-url', 'BusinessController@checkURL');
            $api->get('type-category', 'BusinessController@getTypeAndCategories');

            $api->group(['prefix' => 'member', 'middleware' => ['member.auth']], function ($api) {
                $api->get('/{member}/get-info', 'MemberController@getInfo');
                $api->get('/{member}/get-profile-info', 'MemberController@getProfileInfo');
                $api->post('/{member}/update-personal-info', 'MemberController@updatePersonalInfo');
                $api->post('/{member}/update-professional-info', 'MemberController@updateProfessionalInfo');
                $api->post('/{member}/change-NID', 'MemberController@changeNID');

                $api->post('/{member}/create-business', 'BusinessController@create');
                $api->post('{member}/check-business', 'BusinessController@checkBusiness');
                $api->get('/{member}/show', 'BusinessController@show');

                $api->get('{member}/business/{business}', 'BusinessController@getBusiness');
                $api->post('{member}/business/{business}/update', 'BusinessController@update');
                $api->post('{member}/business/{business}/change-logo', 'BusinessController@changeLogo');
                $api->get('{member}/business/{business}/members', 'BusinessController@getMembers');
                $api->get('{member}/business/{business}/requests', 'BusinessController@getRequests');
                $api->post('{member}/business/{business}/manage-invitation', 'BusinessController@manageInvitation');
                $api->get('{member}/business/{business}/get-member', 'BusinessMemberController@getMember');
                $api->post('{member}/business/{business}/change-member-type', 'BusinessMemberController@changeMemberType');


                $api->post('{member}/search', 'SearchController@searchBusinessOrMember');
                $api->get('{member}/requests', 'MemberController@getRequests');

                $api->post('{member}/send-invitation', 'InvitationController@sendInvitation');
                $api->post('{member}/manage-invitation', 'MemberController@manageInvitation');
            });
        });
        $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
            $api->get('dashboard', 'PartnerController@getDashboardInfo');
            $api->get('earnings', 'PartnerController@getEarnings');
            $api->get('reviews', 'PartnerController@getReviewInfo');
            $api->get('info', 'PartnerController@getInfo');
            $api->get('notifications', 'PartnerController@getNotifications');

            $api->group(['prefix' => 'withdrawals'], function ($api) {
                $api->get('/', 'PartnerWithdrawalRequestController@index');
                $api->post('/', 'PartnerWithdrawalRequestController@store');
                $api->put('{withdrawals}', 'PartnerWithdrawalRequestController@update');
                $api->get('status', 'PartnerWithdrawalRequestController@getStatus');
            });
            $api->group(['prefix' => 'transactions'], function ($api) {
                $api->get('/', 'PartnerTransactionController@index');
            });

            $api->group(['prefix' => 'graphs'], function ($api) {
                $api->get('orders', 'GraphController@getOrdersGraph');
                $api->get('sales', 'GraphController@getSalesGraph');
            });
            $api->group(['prefix' => 'resources'], function ($api) {
                $api->get('/', 'PartnerController@getResources');

                $api->group(['prefix' => '{resource}', 'middleware' => ['partner_resource.auth']], function ($api) {
                    $api->get('/', 'ResourceController@show');
                    $api->get('reviews', 'ResourceController@getReviews');
                });
            });
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->get('/', 'PartnerJobController@index');

                $api->group(['prefix' => '{job}', 'middleware' => ['partner_job.auth']], function ($api) {
                    $api->post('accept', 'PartnerJobController@acceptJobAndAssignResource');
                    $api->post('reject', 'PartnerJobController@declineJob');
                    $api->put('/', 'PartnerJobController@update');

                    $api->group(['prefix' => 'materials'], function ($api) {
                        $api->get('/', 'PartnerJobController@getMaterials');
                        $api->post('/', 'PartnerJobController@addMaterial');
                        $api->put('/', 'PartnerJobController@updateMaterial');
                    });
                });
            });
            $api->group(['prefix' => 'orders'], function ($api) {
                $api->get('new', 'PartnerOrderController@newOrders');
                $api->get('/', 'PartnerOrderController@getOrders');

                $api->group(['prefix' => '{order}', 'middleware' => ['partner_order.auth']], function ($api) {
                    $api->get('/', 'PartnerOrderController@show');
                    $api->get('bills', 'PartnerOrderController@getBillsV1');
                    $api->get('logs', 'PartnerOrderController@getLogs');
                    $api->get('payments', 'PartnerOrderController@getPayments');
                    $api->post('comments', 'PartnerOrderController@postComment');
                });
            });
        });
        $api->group(['prefix' => 'resources/{resource}', 'middleware' => ['resource.auth']], function ($api) {
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->get('/', 'ResourceJobController@index');
                $api->group(['prefix' => '{job}', 'middleware' => ['resource_job.auth']], function ($api) {
                    $api->get('/', 'ResourceJobController@show');
                    $api->put('/', 'ResourceJobController@update');
                    $api->get('others', 'ResourceJobController@otherJobs');
                    $api->post('payment', 'ResourceJobController@collect');
                });
            });
            $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
                $api->get('/', 'ResourceJobController@index');
            });
        });
        $api->group(['prefix' => 'affiliate/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->post('edit', 'AffiliateController@edit');
            $api->post('update-profile-picture', 'AffiliateController@updateProfilePic');
            $api->get('lead-info', 'AffiliateController@leadInfo');

            $api->get('wallet', 'AffiliateController@getWallet');
            $api->get('status', 'AffiliateController@getStatus');
            $api->get('affiliations', 'AffiliationController@index');
            $api->post('affiliations', 'AffiliationController@create');
            $api->post('partner-affiliates', 'PartnerAffiliationController@store');
            $api->get('partner-affiliates', 'PartnerAffiliationController@index');
        });
        $api->group(['prefix' => 'affiliates/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->post('edit', 'AffiliateController@edit');
            $api->get('leads', 'AffiliateController@leadInfo');
            $api->get('notifications', 'AffiliateController@getNotifications');

            $api->get('wallet', 'AffiliateController@getWallet');
            $api->get('status', 'AffiliateController@getStatus');
            $api->get('dashboard', 'AffiliateController@getDashboardInfo');
            $api->get('affiliations', 'AffiliationController@newIndex');
            $api->post('affiliations', 'AffiliationController@create');
            $api->get('transactions', 'AffiliateController@getTransactions');

            $api->get('leaderboard', 'AffiliateController@getLeaderboard');
            $api->group(['prefix' => 'ambassador'], function ($api) {
                $api->get('/', 'AffiliateController@getGodFather');
                $api->get('code', 'AffiliateController@getAmbassador');
                $api->post('code', 'AffiliateController@joinClan');
                $api->get('agents', 'AffiliateController@getAgents');
                $api->get('summary', 'AffiliateController@getAmbassadorSummary');
            });
        });
        $api->group(['prefix' => 'profile', 'middleware' => ['profile.auth']], function ($api) {
            $api->post('change-picture', 'ProfileController@changePicture');
        });

    });
    $api->group(['prefix' => 'v2', 'namespace' => 'App\Http\Controllers'], function ($api) {
        $api->post('subscription', 'PushSubscriptionController@store');
        $api->get('car-rental-info', 'ShebaController@sendCarRentalInfo');
        $api->get('butcher-info', 'ShebaController@sendButcherInfo');
        $api->post('service-requests', 'ServiceRequestController@store');
        $api->post('password/email', 'Auth\PasswordController@sendResetPasswordEmail');
        $api->post('password/validate', 'Auth\PasswordController@validatePasswordResetCode');
        $api->post('password/reset', 'Auth\PasswordController@reset');
        $api->group(['prefix' => 'orders'], function ($api) {
            $api->get('online', 'OrderController@clearPayment');
            $api->group(['prefix' => 'payments'], function ($api) {
                $api->post('success', 'OnlinePaymentController@success');
                $api->post('fail', 'OnlinePaymentController@fail');
                $api->post('cancel', 'OnlinePaymentController@fail');
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
        $api->group(['prefix' => 'job_service'], function ($api) {
            $api->post('/', 'JobServiceController@store');
        });
        $api->group(['prefix' => 'partners'], function ($api) {
            $api->group(['prefix' => '{partner}'], function ($api) {
                $api->get('/', 'PartnerController@show');
                $api->get('locations', 'PartnerController@getLocations');
                $api->get('categories', 'PartnerController@getCategories');
                $api->get('categories/{category}/services', 'PartnerController@getServices');
            });
        });
        $api->group(['prefix' => 'customers'], function ($api) {
            $api->group(['prefix' => '{customer}', 'middleware' => ['customer.auth']], function ($api) {
                $api->get('checkout-info', 'CustomerController@getDeliveryInfo');
                $api->put('notifications', 'CustomerNotificationController@update');
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
                    $api->post('/', 'CustomerDeliveryAddressController@store');
                    $api->put('{delivery_address}', 'CustomerDeliveryAddressController@update');
                    $api->delete('{delivery_address}', 'CustomerDeliveryAddressController@destroy');
                });
                $api->group(['prefix' => 'orders'], function ($api) {
                    $api->post('/', 'OrderController@store');
                    $api->get('/', 'CustomerOrderController@index');
                    $api->get('valid', 'OrderController@checkOrderValidity');
                    $api->get('payment/valid', 'OrderController@checkInvoiceValidity');
                    $api->post('promotions', 'PromotionController@autoApplyPromotion');
                    $api->post('promotions/add', 'PromotionController@addPromotion');
                    $api->group(['prefix' => '{order}'], function ($api) {
                        $api->get('/', 'CustomerOrderController@show');
                    });
                });
                $api->group(['prefix' => 'jobs'], function ($api) {
                    $api->get('/', 'JobController@index');
                    $api->group(['prefix' => '{job}', 'middleware' => ['customer_job.auth']], function ($api) {
                        $api->get('/', 'JobController@show');
                        $api->get('bills', 'JobController@getBills');
                        $api->get('bills/clear', 'JobController@clearBills');
                        $api->get('logs', 'JobController@getLogs');
                        $api->post('reviews', 'ReviewController@store');
                        $api->group(['prefix' => 'complains'], function ($api) {
                            $api->get('/', 'ComplainController@index');
                            $api->post('/', 'ComplainController@store');
                            $api->group(['prefix' => '{complain}'], function ($api) {
                                $api->post('/', 'ComplainController@postComment');
                                $api->get('/', 'ComplainController@show');
                            });
                        });
                        $api->group(['prefix' => 'rates'], function ($api) {
                            $api->get('/', 'RateController@index');
                            $api->post('/', 'RateController@store');
                        });
                    });
                });
            });
        });
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
            $api->post('categories', 'Partner\OperationController@saveCategories');
            $api->get('search', 'SearchController@search');
            $api->group(['prefix' => 'subscriptions'], function ($api) {
                $api->get('/', 'Partner\PartnerSubscriptionController@index');
                $api->post('/', 'Partner\PartnerSubscriptionController@store');
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
            $api->post('job_service/{job_service}/update', 'JobServiceController@update');
            $api->get('get-profile', 'ResourceController@getResourceData');
        });
        $api->group(['prefix' => 'affiliates/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->get('dashboard', 'AffiliateController@getDashboardInfo');
            $api->get('partner-affiliates', 'PartnerAffiliationController@index');
            $api->post('partner-affiliates', 'PartnerAffiliationController@store');
            $api->post('top-up', 'TopUpController@topUp');
        });
    });
});
