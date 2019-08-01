<?php namespace App\Http\Route\Prefix\V2\Partner\ID\NonAuth;


class PosRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'pos'], function ($api) {
            $api->get('shop-products', 'Partner\PartnerPosController@getShopProducts');
        });
    }
}