<?php namespace App\Http\Route\Prefix\V2;

class AffiliateRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'affiliates/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->get('dashboard', 'AffiliateController@getDashboardInfo');
            $api->get('partner-affiliates', 'PartnerAffiliationController@index');
            $api->get('services', 'AffiliateController@getServicesInfo');
            $api->post('partner-affiliates', 'PartnerAffiliationController@store');
            $api->post('top-up', 'TopUpController@topUp');
            $api->post('recharge', 'AffiliateController@rechargeWallet');
            $api->get('service-lead-status', 'AffiliateController@leadInfo');
            $api->get('history', 'AffiliateController@history');
            $api->get('lifetime-gift/{agent_id}', 'AffiliateController@lifeTimeGift');
            $api->get('top-up/validate/ssl', 'SslController@validateTopUp');
            $api->get('top-up/ssl/balance', 'SslController@checkBalance');
        });
        $api->get('affiliates/faq', 'FaqController@getAffiliateFaqs');
    }
}