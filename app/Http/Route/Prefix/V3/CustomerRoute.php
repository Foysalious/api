<?php namespace App\Http\Route\Prefix\V3;

class CustomerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'customers/{customer}', 'middleware' => ['customer.auth']], function ($api) {
            $api->get('preferred-partners', 'Customer\CustomerPartnerController@getPreferredPartners');
            $api->get('order-again', 'Customer\CustomerController@getOrderAgain');
            $api->get('profile-complete','Customer\CustomerController@getProfileCompletion');

            $api->group(['prefix' => 'orders'], function ($api) {
                $api->post('/', 'Order\OrderController@store');
                $api->post('promotions', 'PromotionV3Controller@autoApplyPromotion');
                $api->post('promotions/add', 'PromotionV3Controller@add');
            });
            $api->group(['prefix' => 'edit'], function ($api) {
                $api->put('/', 'CustomerController@updateV3');
            });
            $api->group(['prefix' => 'addresses'], function ($api) {
                $api->post('/', 'Customer\CustomerAddressController@store');
                $api->group(['prefix' => '{address}'], function ($api) {
                    $api->get('available', 'Customer\CustomerAddressController@isAvailable');
                });
            });
        });

        $api->group(['prefix' => 'customers'], function ($api) {
            $api->group(['prefix' => 'info-call'], function ($api) {
                $api->post('/', 'InfoCall\InfoCallController@store');
            });
        });
    }
}
