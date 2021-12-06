<?php namespace App\Http\Route\Prefix\V2\Partner\ID\NonAuth;

class IndexRoute
{
    public function set($api)
    {
        $api->get('by-domain-name', 'PartnerController@showByDomain');
        $api->group(['prefix' => '{partner}'], function ($api) {
            $api->get('test-push-notification', 'PushSubscriptionController@sendV2');
            (new PosRoute())->set($api);
            $api->get('/', 'PartnerController@show');
            $api->get('locations', 'PartnerController@getLocations');
            $api->get('locations/all', 'LocationController@getPartnerServiceLocations');
            $api->get('categories', 'PartnerController@getCategories');
            $api->get('categories/{category}/services', 'PartnerController@getServices');
            $api->get('categories/{category}/addable-services', 'PartnerController@getAddableServices');
        });
    }
}
