<?php namespace App\Http\Route\Prefix\V2;


class BusinessRoute
{
    public function set($api)
    {
        $api->post('business/login', 'B2b\LoginController@login');
        $api->post('business/register', 'B2b\RegistrationController@register');

        $api->group(['prefix' => 'businesses', 'middleware'=>['business.auth']], function ($api) {

            $api->group(['prefix' => '{business}'], function ($api) {
                $api->get('/vendors', 'B2b\MembersController@getVendorsInfo');
                $api->post('/invite', 'B2b\BusinessesController@inviteVendors');
                $api->post('orders', 'B2b\OrderController@placeOrder');
                $api->post('promotions/add', 'B2b\OrderController@applyPromo');
                $api->group(['prefix' => 'orders', 'middleware' => ['business_order.auth']], function ($api) {
                    $api->get('/', 'B2b\OrderController@index');
                    $api->group(['prefix' => '{order}'], function ($api) {
                        $api->get('bills/clear', 'B2b\OrderController@clearBills');
                    });
                });
            });
        });
    }
}