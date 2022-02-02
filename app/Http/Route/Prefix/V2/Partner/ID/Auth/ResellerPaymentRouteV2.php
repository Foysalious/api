<?php

namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;

class ResellerPaymentRouteV2
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners', 'middleware' => ['paymentLink.auth']], function ($api) {
            $api->group(['prefix' => 'reseller-payment'], function ($api) {
                $api->get('/store-configuration', 'ResellerPayment\\StoreConfigurationController@getV2');
            });
        });
    }
}