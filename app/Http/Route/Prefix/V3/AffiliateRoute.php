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

            $api->group(['prefix' => 'bondhu-balance'], function ($api) {
                $api->post('purchase', 'BondhuBalanceController@purchase');
                $api->post('validate', 'BondhuBalanceController@validatePayment');
            });
        });
    }
}
