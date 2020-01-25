<?php namespace App\Http\Route\Prefix\V3;

class AffiliateRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'affiliates/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->group(['prefix' => 'orders'], function ($api) {
                $api->post('/', 'Order\OrderController@storeFromBondhu');
            });
        });
    }
}
