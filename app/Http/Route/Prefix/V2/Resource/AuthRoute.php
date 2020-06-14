<?php namespace App\Http\Route\Prefix\V2\Resource;


class AuthRoute
{
    public function set($api)
    {
        $api->group(['middleware' => 'resource.jwt.auth'], function ($api) {
            $api->get('profile', 'Resource\ResourceController@getProfile');
            $api->get('home', 'Resource\ResourceController@getHome');
            $api->get('dashboard', 'Resource\ResourceController@dashboard');
            $api->get('test-notification', 'Resource\ResourceNotificationController@test');
            $api->get('notifications', 'Resource\ResourceNotificationController@index');
            $api->post('notifications/seen', 'Resource\ResourceNotificationController@seen');
            $api->get('help', 'Resource\ResourceController@help');
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->get('/', 'Resource\ResourceJobController@index');
                $api->get('all', 'Resource\ResourceJobController@getAllJobs');
                $api->get('next', 'Resource\ResourceJobController@getNextJob');
                $api->group(['prefix' => '{job}'], function ($api) {
                    $api->get('schedules', 'Resource\ResourceController@getSchedules');
                    $api->get('/', 'Resource\ResourceJobController@jobDetails');
                    $api->post('status', 'Resource\ResourceJobController@updateStatus');
                    $api->post('reschedule', 'Resource\ResourceJobController@rescheduleJob');
                    $api->post('collection', 'Resource\ResourceJobController@collectMoney');
                    $api->get('bills', 'Resource\ResourceJobController@getBills');
                    $api->get('rates', 'Resource\ResourceJobRateController@index');
                    $api->post('rating', 'Resource\ResourceJobRateController@storeCustomerRating');
                    $api->post('review', 'Resource\ResourceJobRateController@storeCustomerReview');
                    $api->post('extend-time', 'Resource\ResourceJobController@extendTime');
                    $api->get('services', 'Resource\ResourceJobController@getServices');
                    $api->get('updated-bill', 'Resource\ResourceJobController@getUpdatedBill');
                    $api->post('services', 'Resource\ResourceJobController@updateService');
                });
            });
            $api->group(['prefix' => 'transactions'], function ($api) {
                $api->get('/', 'Resource\ResourceTransactionController@index');
            });
            $api->group(['prefix' => 'rewards'], function ($api) {
                $api->get('/', 'Resource\ResourceRewardController@index');
                $api->get('history', 'Resource\ResourceRewardController@history');
                $api->group(['prefix' => 'campaigns'], function ($api) {
                    $api->group(['prefix' => '{campaign}'], function ($api) {
                        $api->get('/', 'Resource\Reward\CampaignController@show');
                    });
                });
                $api->group(['prefix' => '{reward}'], function ($api) {
                    $api->get('/', 'Resource\ResourceRewardController@show');
                });
            });
            $api->get('wallet', 'Resource\ResourceWalletController@getWallet');
            $api->post('withdrawals', 'Resource\ResourceWithdrawalRequestController@store');
        });
    }
}