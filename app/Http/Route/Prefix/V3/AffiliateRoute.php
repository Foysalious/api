<?php namespace App\Http\Route\Prefix\V3;

class AffiliateRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'affiliates/{affiliate}', 'middleware' => ['affiliate.auth']], function ($api) {
            $api->group(['prefix' => 'orders'], function ($api) {
                $api->post('/', 'Order\OrderController@storeFromBondhu');
            });
            $api->get('notifications', 'AffiliateController@getNotifications');
            $api->get('notifications/{notification}', 'AffiliateController@getNotification');
            $api->get('notification-seen/{id}', 'B2b\BusinessesController@notificationSeen');
            $api->post('top-up', 'TopUpController@topUpWithPin');
            $api->post('top-up-otf', 'TopUpController@topUpOTF');
            $api->post('top-up-otf-details', 'TopUpController@topUpOTFDetails');

            $api->group(['prefix' => 'bondhu-balance'], function ($api) {
                $api->post('purchase', 'BondhuBalanceController@purchase');
                $api->post('validate', 'BondhuBalanceController@validatePayment');
            });

            $api->group(['prefix' => 'bondhu-reward'], function ($api){
                $api->get('/', 'Affiliate\BondhuRewardController@rewardList');
                $api->get('history', 'Affiliate\BondhuRewardController@rewardHistory');
                $api->get('achieved', 'Affiliate\BondhuRewardController@getUnseenAchievedRewards');
                $api->get('{rewardId}', 'Affiliate\BondhuRewardController@rewardDetails')->where('rewardId', '[0-9]+');
                $api->put('seen', 'Affiliate\BondhuRewardController@updateIsSeen');
            });
        });


//        $api->get('affiliates/{affiliate}/bondhu-reward/history', 'Affiliate\BondhuRewardController@rewardHistory' );


    }
}
