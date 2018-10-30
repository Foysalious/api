<?php

namespace App\Http\Route\Prefix\V2;


class AffiliateRoute
{
    public function set($api)
    {
        $api->get('affiliates/{affiliate}/service-lead-status', 'AffiliateController@leadInfo');
        $api->group(['prefix' => 'affiliates/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->get('dashboard', 'AffiliateController@getDashboardInfo');
            $api->get('partner-affiliates', 'PartnerAffiliationController@index');
            $api->get('services', 'AffiliateController@getServicesInfo');
            $api->post('partner-affiliates', 'PartnerAffiliationController@store');
            $api->post('top-up', 'TopUpController@topUp');
            $api->post('recharge', 'AffiliateController@rechargeWallet');
            //$api->get('service-lead-status', 'AffiliateController@leadInfo');
        });
    }
}