<?php

Route::get('/', function () {
    $resources = (\App\Models\Partner::find(3))->resources;
    foreach ($resources as $resource) {
        if ($resource->pivot->resource_type == 'Handyman' && $resource->pivot->is_verified) {
            $schedule = new \App\Models\ResourceSchedule();
            $schedule->job_id = 1;
            $schedule->start = '00:00:00-01:00:00';
            $schedule->end = '23:00:00-24:00:00';
            $schedule->save();
        }
    }
    return ['code' => 200, 'msg' => "Success. This project will hold the api's"];
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
    $api->group(['prefix' => 'v1'], function ($api) {
        $api->post('login', 'App\Http\Controllers\Auth\LoginController@login');
        $api->post('register', 'App\Http\Controllers\Auth\RegistrationController@register');
        $api->post('continue-with-kit', 'App\Http\Controllers\FacebookController@continueWithKit');
        $api->post('continue-with-facebook', 'App\Http\Controllers\FacebookController@continueWithFacebook');
        $api->post('send-password-reset-email', 'App\Http\Controllers\Auth\PasswordController@sendResetPasswordEmail');
        $api->post('reset-password', 'App\Http\Controllers\Auth\PasswordController@resetPassword');

        $api->get('authenticate', 'App\Http\Controllers\AccountController@checkForAuthentication');
        $api->post('account', 'App\Http\Controllers\AccountController@encryptData');
        $api->get('decrypt', 'App\Http\Controllers\AccountController@decryptData');

        $api->get('info', 'App\Http\Controllers\ShebaController@getInfo');
        $api->get('versions', 'App\Http\Controllers\ShebaController@getVersions');
        $api->get('images', 'App\Http\Controllers\ShebaController@getImages');
        $api->get('locations', 'App\Http\Controllers\LocationController@getAllLocations');
        $api->get('lead-reward', 'App\Http\Controllers\ShebaController@getLeadRewardAmount');
        $api->get('search', 'App\Http\Controllers\SearchController@searchService');
        $api->get('career', 'App\Http\Controllers\CareerController@getVacantPosts');
        $api->post('career', 'App\Http\Controllers\CareerController@apply');
        $api->get('category-service', 'App\Http\Controllers\CategoryServiceController@getCategoryServices');
        $api->get('job-times', 'App\Http\Controllers\JobController@getPreferredTimes');
        $api->get('times', 'App\Http\Controllers\JobController@getPreferredTimes');
        $api->get('cancel-job-reasons', 'App\Http\Controllers\JobController@cancelJobReasons');

        $api->post('voucher-valid', 'App\Http\Controllers\CheckoutController@validateVoucher');
        $api->post('vouchers', 'App\Http\Controllers\CheckoutController@validateVoucher');

        $api->post('rating', 'App\Http\Controllers\ReviewController@giveRatingFromEmail');
        $api->post('sms', 'App\Http\Controllers\SmsController@send');
        $api->post('faq', 'App\Http\Controllers\ShebaController@sendFaq');
        $api->get('offers', 'App\Http\Controllers\ShebaController@getOffers');
        $api->get('offer/{offer}', 'App\Http\Controllers\ShebaController@getOffer');
        $api->get('offer/{offer}/similar', 'App\Http\Controllers\ShebaController@getSimilarOffer');

        $api->group(['prefix' => 'navigation'], function ($api) {
            $api->get('/', 'App\Http\Controllers\NavigationController@getNavList');
        });
        $api->group(['prefix' => 'jobs'], function ($api) {
            $api->get('times', 'App\Http\Controllers\JobController@getPreferredTimes');
        });
        $api->group(['prefix' => 'locations'], function ($api) {
            $api->get('current', 'App\Http\Controllers\LocationController@getCurrent');
        });
        $api->group(['prefix' => 'categories'], function ($api) {
            $api->get('/', 'App\Http\Controllers\CategoryController@index');
            $api->get('{category}/secondaries', 'App\Http\Controllers\CategoryController@getSecondaries');
            $api->get('{category}/secondaries/services', 'App\Http\Controllers\CategoryController@getSecondaryServices');
            $api->get('{category}/services', 'App\Http\Controllers\CategoryController@getServices');
            $api->get('{category}/master', 'App\Http\Controllers\CategoryController@getMaster');
        });
        $api->group(['prefix' => 'service'], function ($api) {
            $api->get('{service}/get-prices', 'App\Http\Controllers\ServiceController@getPrices');
            $api->get('{service}/location/{location}/partners', 'App\Http\Controllers\ServiceController@getPartners');
            $api->post('{service}/location/{location}/partners', 'App\Http\Controllers\ServiceController@getPartners');
            $api->post('{service}/{location}/change-partner', 'App\Http\Controllers\ServiceController@changePartner');
            $api->get('/{service}/reviews', 'App\Http\Controllers\ServiceController@getReviews');
            //For Back-end
            $api->post('{service}/change-partner', 'App\Http\Controllers\ServiceController@changePartnerWithoutLocation');
        });
        $api->group(['prefix' => 'services'], function ($api) {
            $api->get('/', 'App\Http\Controllers\ServiceController@index');
            $api->get('{service}', 'App\Http\Controllers\ServiceController@get');
            $api->get('{service}/valid', 'App\Http\Controllers\ServiceController@checkForValidity');
            $api->get('{service}/similar', 'App\Http\Controllers\ServiceController@getSimilarServices');
            $api->get('{service}/reviews', 'App\Http\Controllers\ServiceController@getReviews');
            $api->get('{service}/locations/{location}/partners', 'App\Http\Controllers\ServiceController@getPartnersOfLocation');
            $api->post('{service}/locations/{location}/partners', 'App\Http\Controllers\ServiceController@getPartners');
        });
        $api->group(['prefix' => 'partner'], function ($api) {
            $api->get('/', 'App\Http\Controllers\PartnerController@index');
            $api->get('{partner}/services', 'App\Http\Controllers\PartnerController@getPartnerServices');
            $api->get('{partner}/reviews', 'App\Http\Controllers\PartnerController@getReviews');
        });
        $api->group(['prefix' => 'customer', 'middleware' => ['customer.auth']], function ($api) {
            $api->get('{customer}', 'App\Http\Controllers\CustomerController@getCustomerInfo');
            $api->post('{customer}/edit', 'App\Http\Controllers\CustomerController@editInfo');
            $api->get('{customer}/general-info', 'App\Http\Controllers\CustomerController@getCustomerGeneralInfo');
            $api->get('{customer}/intercom-info', 'App\Http\Controllers\CustomerController@getIntercomInfo');
            $api->get('{customer}/checkout-info', 'App\Http\Controllers\CustomerController@getDeliveryInfo');
            $api->get('{customer}/order-list', 'App\Http\Controllers\OrderController@getNotClosedOrderInfo');
            $api->get('{customer}/order-history', 'App\Http\Controllers\OrderController@getClosedOrderInfo');
            $api->get('{customer}/cancel-order-list', 'App\Http\Controllers\OrderController@getCancelledOrders');
            $api->get('{customer}/referral', 'App\Http\Controllers\CustomerController@getReferral');
            $api->post('{customer}/send-referral-request-email', 'App\Http\Controllers\CustomerController@sendReferralRequestEmail');
            $api->get('{customer}/promo', 'App\Http\Controllers\PromotionController@getPromo');
            $api->post('{customer}/promo', 'App\Http\Controllers\PromotionController@addPromo');
            $api->post('{customer}/suggest-promo', 'App\Http\Controllers\PromotionController@suggestPromo');

            $api->post('{customer}/sp-payment', 'App\Http\Controllers\CheckoutController@spPayment');
            $api->post('{customer}/order-valid', 'App\Http\Controllers\OrderController@checkOrderValidity');
            $api->post('{customer}/modify-review', 'App\Http\Controllers\ReviewController@modifyReview');
            $api->get('{customer}/job/{job}', 'App\Http\Controllers\JobController@getInfo');
            $api->post('{customer}/{job}/cancel', 'App\Http\Controllers\JobController@cancelJob');

            $api->post('{customer}/ask-quotation', 'App\Http\Controllers\CustomOrderController@askForQuotation');
            $api->get('{customer}/custom-order', 'App\Http\Controllers\CustomOrderController@getCustomOrders');
            $api->get('{customer}/custom-order/{custom_order}/quotation', 'App\Http\Controllers\CustomOrderController@getCustomOrderQuotation');
            $api->get('{customer}/custom-order/{custom_order}/discussion', 'App\Http\Controllers\CustomOrderController@getCommentForDiscussion');
            $api->post('{customer}/custom-order/{custom_order}/discussion', 'App\Http\Controllers\CustomOrderController@postCommentOnDiscussion');

            $api->post('{customer}/checkout/place-order', 'App\Http\Controllers\CheckoutController@placeOrder');
            $api->post('{customer}/checkout/place-order-with-online-payment', 'App\Http\Controllers\CheckoutController@placeOrderWithPayment');

//        $api->post('{customer}/fb-integration', 'App\Http\Controllers\CustomerController@facebookIntegration');
//        $api->post('{customer}/change-address', 'App\Http\Controllers\CustomerController@changeAddress');
//        $api->post('{customer}/add-delivery-address', 'App\Http\Controllers\CustomerController@addDeliveryAddress');
//        $api->post('{customer}/remove-address', 'App\Http\Controllers\CustomerController@removeDeliveryAddress');
//        $api->post('{customer}/mobile', 'App\Http\Controllers\CustomerController@modifyMobile');
//        $api->post('{customer}/add-secondary-mobile', 'App\Http\Controllers\CustomerController@addSecondaryMobile');
//        $api->post('{customer}/remove-secondary-mobile', 'App\Http\Controllers\CustomerController@removeSecondaryMobile');
//        $api->post('{customer}/set-primary-mobile', 'App\Http\Controllers\CustomerController@setPrimaryMobile');
//        $api->post('{customer}/email', 'App\Http\Controllers\CustomerController@modifyEmail');
//        $api->post('{customer}/email-verification', 'App\Http\Controllers\CustomerController@checkEmailVerification');
//        $api->post('{customer}/send-verification-link', 'App\Http\Controllers\CustomerController@sendVerificationLink');

        });
        $api->group(['prefix' => 'customers/{customer}', 'middleware' => ['customer.auth']], function ($api) {
            $api->post('reviews', 'App\Http\Controllers\ReviewController@modifyReview');
            $api->get('notifications', 'App\Http\Controllers\CustomerController@getNotifications');
            $api->post('suggest-promo', 'App\Http\Controllers\PromotionController@suggestPromo');
            $api->put('addresses/{address}', 'App\Http\Controllers\CustomerAddressController@update');
            $api->group(['prefix' => 'favorites'], function ($api) {
                $api->get('/', 'App\Http\Controllers\CustomerFavoriteController@index');
                $api->post('/', 'App\Http\Controllers\CustomerFavoriteController@store');
                $api->put('/', 'App\Http\Controllers\CustomerFavoriteController@update');
                $api->delete('{favorite}', 'App\Http\Controllers\CustomerFavoriteController@destroy');
            });
        });
        $api->group(['prefix' => 'checkout'], function ($api) {
            $api->get('place-order-final', 'App\Http\Controllers\CheckoutController@placeOrderFinal');
            $api->get('sp-payment-final', 'App\Http\Controllers\CheckoutController@spPaymentFinal');
        });
        $api->group(['prefix' => 'business'], function ($api) {
            $api->get('check-url', 'App\Http\Controllers\BusinessController@checkURL');
            $api->get('type-category', 'App\Http\Controllers\BusinessController@getTypeAndCategories');

            $api->group(['prefix' => 'member', 'middleware' => ['member.auth']], function ($api) {
                $api->get('/{member}/get-info', 'App\Http\Controllers\MemberController@getInfo');
                $api->get('/{member}/get-profile-info', 'App\Http\Controllers\MemberController@getProfileInfo');
                $api->post('/{member}/update-personal-info', 'App\Http\Controllers\MemberController@updatePersonalInfo');
                $api->post('/{member}/update-professional-info', 'App\Http\Controllers\MemberController@updateProfessionalInfo');
                $api->post('/{member}/change-NID', 'App\Http\Controllers\MemberController@changeNID');

                $api->post('/{member}/create-business', 'App\Http\Controllers\BusinessController@create');
                $api->post('{member}/check-business', 'App\Http\Controllers\BusinessController@checkBusiness');
                $api->get('/{member}/show', 'App\Http\Controllers\BusinessController@show');

                $api->get('{member}/business/{business}', 'App\Http\Controllers\BusinessController@getBusiness');
                $api->post('{member}/business/{business}/update', 'App\Http\Controllers\BusinessController@update');
                $api->post('{member}/business/{business}/change-logo', 'App\Http\Controllers\BusinessController@changeLogo');
                $api->get('{member}/business/{business}/members', 'App\Http\Controllers\BusinessController@getMembers');
                $api->get('{member}/business/{business}/requests', 'App\Http\Controllers\BusinessController@getRequests');
                $api->post('{member}/business/{business}/manage-invitation', 'App\Http\Controllers\BusinessController@manageInvitation');
                $api->get('{member}/business/{business}/get-member', 'App\Http\Controllers\BusinessMemberController@getMember');
                $api->post('{member}/business/{business}/change-member-type', 'App\Http\Controllers\BusinessMemberController@changeMemberType');


                $api->post('{member}/search', 'App\Http\Controllers\SearchController@searchBusinessOrMember');
                $api->get('{member}/requests', 'App\Http\Controllers\MemberController@getRequests');

                $api->post('{member}/send-invitation', 'App\Http\Controllers\InvitationController@sendInvitation');
                $api->post('{member}/manage-invitation', 'App\Http\Controllers\MemberController@manageInvitation');
            });
        });
        $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
            $api->get('dashboard', 'App\Http\Controllers\PartnerController@getDashboardInfo');
            $api->get('earnings', 'App\Http\Controllers\PartnerController@getEarnings');
            $api->get('reviews', 'App\Http\Controllers\PartnerController@getReviewInfo');
            $api->get('info', 'App\Http\Controllers\PartnerController@show');
            $api->get('notifications', 'App\Http\Controllers\PartnerController@getNotifications');

            $api->group(['prefix' => 'withdrawals'], function ($api) {
                $api->get('/', 'App\Http\Controllers\PartnerWithdrawalRequestController@index');
                $api->post('/', 'App\Http\Controllers\PartnerWithdrawalRequestController@store');
                $api->put('{withdrawals}', 'App\Http\Controllers\PartnerWithdrawalRequestController@update');
                $api->get('status', 'App\Http\Controllers\PartnerWithdrawalRequestController@getStatus');
            });
            $api->group(['prefix' => 'transactions'], function ($api) {
                $api->get('/', 'App\Http\Controllers\PartnerTransactionController@index');
            });

            $api->group(['prefix' => 'graphs'], function ($api) {
                $api->get('orders', 'App\Http\Controllers\GraphController@getOrdersGraph');
                $api->get('sales', 'App\Http\Controllers\GraphController@getSalesGraph');
            });
            $api->group(['prefix' => 'resources'], function ($api) {
                $api->get('/', 'App\Http\Controllers\PartnerController@getResources');

                $api->group(['prefix' => '{resource}', 'middleware' => ['partner_resource.auth']], function ($api) {
                    $api->get('/', 'App\Http\Controllers\ResourceController@show');
                    $api->get('reviews', 'App\Http\Controllers\ResourceController@getReviews');
                });
            });
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->get('/', 'App\Http\Controllers\PartnerJobController@index');

                $api->group(['prefix' => '{job}', 'middleware' => ['partner_job.auth']], function ($api) {
                    $api->post('accept', 'App\Http\Controllers\PartnerJobController@acceptJobAndAssignResource');
                    $api->post('reject', 'App\Http\Controllers\PartnerJobController@declineJob');
                    $api->put('/', 'App\Http\Controllers\PartnerJobController@update');

                    $api->group(['prefix' => 'materials'], function ($api) {
                        $api->get('/', 'App\Http\Controllers\PartnerJobController@getMaterials');
                        $api->post('/', 'App\Http\Controllers\PartnerJobController@addMaterial');
                        $api->put('/', 'App\Http\Controllers\PartnerJobController@updateMaterial');
                    });
                });
            });
            $api->group(['prefix' => 'orders'], function ($api) {
                $api->get('new', 'App\Http\Controllers\PartnerOrderController@newOrders');
                $api->get('/', 'App\Http\Controllers\PartnerOrderController@getOrders');

                $api->group(['prefix' => '{order}', 'middleware' => ['partner_order.auth']], function ($api) {
                    $api->get('/', 'App\Http\Controllers\PartnerOrderController@show');
                    $api->get('bills', 'App\Http\Controllers\PartnerOrderController@getBills');
                    $api->get('logs', 'App\Http\Controllers\PartnerOrderController@getLogs');
                    $api->get('payments', 'App\Http\Controllers\PartnerOrderController@getPayments');
                    $api->post('comments', 'App\Http\Controllers\PartnerOrderController@postComment');
                });
            });
        });
        $api->group(['prefix' => 'resources/{resource}', 'middleware' => ['resource.auth']], function ($api) {
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->get('/', 'App\Http\Controllers\ResourceJobController@index');
                $api->group(['prefix' => '{job}', 'middleware' => ['resource_job.auth']], function ($api) {
                    $api->get('/', 'App\Http\Controllers\ResourceJobController@show');
                    $api->put('/', 'App\Http\Controllers\ResourceJobController@update');
                    $api->get('others', 'App\Http\Controllers\ResourceJobController@otherJobs');
                    $api->post('payment', 'App\Http\Controllers\ResourceJobController@collect');
                });
            });
            $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
                $api->get('/', 'App\Http\Controllers\ResourceJobController@index');
            });
        });
        $api->group(['prefix' => 'affiliate/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->post('edit', 'App\Http\Controllers\AffiliateController@edit');
            $api->post('update-profile-picture', 'App\Http\Controllers\AffiliateController@updateProfilePic');
            $api->get('lead-info', 'App\Http\Controllers\AffiliateController@leadInfo');

            $api->get('wallet', 'App\Http\Controllers\AffiliateController@getWallet');
            $api->get('status', 'App\Http\Controllers\AffiliateController@getStatus');
            $api->get('affiliations', 'App\Http\Controllers\AffiliationController@index');
            $api->post('affiliations', 'App\Http\Controllers\AffiliationController@create');
        });
        $api->group(['prefix' => 'affiliates/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->post('edit', 'App\Http\Controllers\AffiliateController@edit');
            $api->get('leads', 'App\Http\Controllers\AffiliateController@leadInfo');
            $api->get('notifications', 'App\Http\Controllers\AffiliateController@getNotifications');

            $api->get('wallet', 'App\Http\Controllers\AffiliateController@getWallet');
            $api->get('status', 'App\Http\Controllers\AffiliateController@getStatus');
            $api->get('affiliations', 'App\Http\Controllers\AffiliationController@newIndex');
            $api->post('affiliations', 'App\Http\Controllers\AffiliationController@create');
            $api->get('transactions', 'App\Http\Controllers\AffiliateController@getTransactions');

            $api->get('leaderboard', 'App\Http\Controllers\AffiliateController@getLeaderboard');
            $api->group(['prefix' => 'ambassador'], function ($api) {
                $api->get('/', 'App\Http\Controllers\AffiliateController@getGodFather');
                $api->get('code', 'App\Http\Controllers\AffiliateController@getAmbassador');
                $api->post('code', 'App\Http\Controllers\AffiliateController@joinClan');
                $api->get('agents', 'App\Http\Controllers\AffiliateController@getAgents');
                $api->get('summary', 'App\Http\Controllers\AffiliateController@getAmbassadorSummary');
            });
        });
        $api->group(['prefix' => 'profile', 'middleware' => ['profile.auth']], function ($api) {
            $api->post('change-picture', 'App\Http\Controllers\ProfileController@changePicture');
        });

    });
    $api->group(['prefix' => 'v2', 'namespace' => 'App\Http\Controllers'], function ($api) {
        $api->get('times', 'ShebaController@getTimeSlots');
        $api->get('locations/{location}/partners', 'PartnerController@findPartners');
        $api->group(['prefix' => 'job_service'], function ($api) {
            $api->post('/', 'JobServiceController@store');
        });
        $api->group(['prefix' => 'customers/{customer}'], function ($api) {
            $api->post('orders', 'OrderController@store');
        });
    });

});
