<?php namespace App\Http\Route\Prefix\V3;

class CustomerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'customers/{customer}', 'middleware' => ['customer.auth']], function ($api) {
            $api->get('preferred-partners', 'Customer\CustomerPartnerController@getPreferredPartners');
            $api->get('order-again', 'Customer\CustomerController@getOrderAgain');

            $api->group(['prefix' => 'orders'], function ($api) {
                $api->post('/', 'Order\OrderController@store');
                $api->get('promotions', 'PromotionV3Controller@autoApplyPromotion');
                $api->post('promotions/add', 'PromotionV3Controller@add');
            });
            $api->group(['prefix' => 'addresses'], function ($api) {
                $api->post('/', 'Customer\CustomerAddressController@store');
                $api->group(['prefix' => '{address}'], function ($api) {
                    $api->get('available', 'Customer\CustomerAddressController@isAvailable');
                });
            });
        });
    }
}
