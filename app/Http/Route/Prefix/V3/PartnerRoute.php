<?php namespace App\Http\Route\Prefix\V3;

class PartnerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['manager.auth']], function ($api) {
            $api->get('dashboard','Partner\DashboardController@getV3');
            $api->get('feature-videos', 'Partner\DashboardController@getFeatureVideos');
            $api->get('home-setting', 'Partner\DashboardController@getHomeSettingV3')->name('partner.home-setting');
            $api->post('home-setting', 'Partner\DashboardController@updateHomeSettingV3');
            $api->get('is-updated-home-setting', 'Partner\DashboardController@isUpdatedHomeSetting');
            $api->post('top-up-otf', 'TopUpController@topUpOTF');
            $api->post('top-up-otf-details', 'TopUpController@topUpOTFDetails');
            $api->get('new-dashboard','Partner\DashboardController@getV3dashboard');
        });
        $api->group(['prefix' => 'partners/{partner}', 'middleware' => ['jwtGlobalAuth']], function ($api) {
            $api->get('setting-updated-at', 'Partner\DashboardController@settingUpdatedDetails')->name('partner.setting-updated-at');
        });
    }
}
