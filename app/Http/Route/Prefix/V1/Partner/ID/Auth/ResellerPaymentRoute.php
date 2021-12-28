<?php namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class ResellerPaymentRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners', 'middleware' => ['jwtGlobalAuth']], function ($api) {
            $api->group(['prefix' => 'reseller-payment'], function ($api) {
                $api->get('/store-configuration', 'ResellerPayment\\StoreConfigurationController@get');
            });
        });
    }
}