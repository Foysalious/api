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
            $api->post('bulk-top-up', 'TopUpController@bulkTopUp');
            $api->post('recharge', 'AffiliateController@rechargeWallet');
            $api->get('service-lead-status', 'AffiliateController@leadInfo');
            $api->get('history', 'AffiliateController@history');
            $api->get('lifetime-gift/{agent_id}', 'AffiliateController@lifeTimeGift');
            $api->get('top-up/validate/ssl', 'SslController@validateTopUp');
            $api->get('top-up/ssl/balance', 'SslController@checkBalance');
            $api->get('top-up/history', 'AffiliateController@topUpHistory');
            $api->post('place-order', 'OrderController@placeOrderFromBondhu');
            $api->get('customer-delivery-address', 'CustomerDeliveryAddressController@getDeliveryInfoForAffiliate');
            $api->post('customer-delivery-address', 'CustomerDeliveryAddressController@storeDeliveryAddressForAffiliate');
            $api->get('customer-info', 'AffiliateController@getCustomerInfo');
            $api->get('partner-info', 'AffiliateController@getPartnerInfo');
            $api->get('personal-info', 'AffiliateController@getPersonalInformation');
            $api->post('top-up-test', 'TopUpController@topUpTest');
            $api->get('onboarded-partners', 'Affiliate\\LitePartnerOnBoardingController@index');
            $api->post('moderate/{partner_id}/accept', 'Affiliate\\LitePartnerOnBoardingController@acceptRequest');
            $api->post('moderate/{partner_id}/reject', 'Affiliate\\LitePartnerOnBoardingController@rejectRequest');
            $api->post('refer', 'Auth\PartnerRegistrationController@registerReferAffiliate');
        });
        $api->post('eksheba/save','EkshebaController@saveEkshebaData');
        $api->get('affiliates/faq', 'FaqController@getAffiliateFaqs');
    }
}