<?php namespace App\Http\Route\Prefix\V3;

class BusinessRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'businesses', 'middleware' => ['business.auth']], function ($api) {
            $api->group(['prefix' => '{business}'], function ($api) {
                $api->get('vendors', 'B2b\BusinessesController@getVendorsListV3');
            });
        });
    }
}
