<?php namespace App\Http\Route\Prefix\V2;


class BusinessRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'businesses'], function ($api) {
            $api->get('{business}/vendors', 'B2b\MembersController@getVendorsInfo');
            $api->post('{business}/invite', 'B2b\BusinessesController@inviteVendors');
            $api->post('orders', 'B2b\OrderController@placeOrder');
        });
    }
}