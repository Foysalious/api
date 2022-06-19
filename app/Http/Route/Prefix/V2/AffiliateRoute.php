<?php namespace App\Http\Route\Prefix\V2;

class AffiliateRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'affiliates/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->get('dashboard', 'AffiliateController@getDashboardInfo');
            $api->get('dashboard-messages', 'AffiliateController@getDashboardMessage');
            $api->get('transaction-history-categories', 'AffiliateTransactionController@getTransactionByCategory');
            $api->get('partner-affiliates', 'PartnerAffiliationController@index');
            $api->get('services', 'AffiliateController@getServicesInfo');
            $api->post('partner-affiliates', 'PartnerAffiliationController@store');
            $api->post('bulk-top-up', 'TopUpController@bulkTopUp');
            $api->post('recharge', 'AffiliateController@rechargeWallet');
            $api->get('service-lead-status', 'AffiliateController@leadInfo');
            $api->get('history', 'AffiliateController@history');
            $api->get('topup-earning', 'AffiliateController@topUpEarning');
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
            $api->get('onboarded-partners', 'Affiliate\\LitePartnerOnBoardingController@index');
            $api->get('lite-sp-reject-reasons', 'Affiliate\\LitePartnerOnBoardingController@rejectReason');
            $api->get('lite-sp-pending', 'Affiliate\\LitePartnerOnBoardingController@litePartners');
            $api->get('lite-sp-history', 'Affiliate\\LitePartnerOnBoardingController@history');
            $api->get('lite-sp-details', 'Affiliate\\LitePartnerOnBoardingController@litePartnerDetails');
            $api->post('moderate/{partner_id}/accept', 'Affiliate\\LitePartnerOnBoardingController@acceptRequest');
            $api->post('moderate/{partner_id}/reject', 'Affiliate\\LitePartnerOnBoardingController@rejectRequest');
            $api->post('refer', 'Auth\PartnerRegistrationController@registerReferAffiliate');
            $api->get('profile-details', 'AffiliateController@profileDetails');
            $api->post('personal-information', 'AffiliateController@updatePersonalInformation');
            $api->post('bank-information', 'AffiliateController@storeBankInformation');
            $api->post('mobile-bank-information', 'AffiliateController@storeMobileBankInformation');
            $api->post('bank-information/{profile_bank_information}', 'AffiliateController@updateBankInformation');
            $api->delete('bank-info/{profile_bank_information}', 'AffiliateController@deleteBankInformation');
            $api->delete('mobile-bank-info/{profile_bank_information}', 'AffiliateController@deleteMobileBankInformation');
            $api->post('mobile-bank-information/{profile_mobile_bank_info}', 'AffiliateController@updateMobileBankInformation');
            $api->get('ddn-order-list', 'AffiliateController@getOrderList');
            $api->get('ddn-order-details/{order}', 'AffiliateController@getOrderDetails');
            $api->group(['prefix' => 'movie-ticket'], function ($api) {
                $api->get('movie-list', 'MovieTicketController@getAvailableTickets');
                $api->get('theatre-list', 'MovieTicketController@getAvailableTheatres');
                $api->get('theatre-seat-status', 'MovieTicketController@getTheatreSeatStatus');
                $api->get('history', 'MovieTicketController@history');
                $api->get('history/{history_id}', 'MovieTicketController@historyDetails');
                $api->post('book-tickets', 'MovieTicketController@bookTickets');
                $api->post('update-status', 'MovieTicketController@updateTicketStatus');
            });
            (new TransportRoute())->set($api);
        });

        $api->get('bank-list', 'AffiliateController@bankList');
        $api->get('mobile-bank-list', 'AffiliateController@mobileBankList');
        $api->post('eksheba/save', 'EkshebaController@saveEkshebaData');
        $api->get('affiliates/faq', 'FaqController@getAffiliateFaqs');
    }
}
