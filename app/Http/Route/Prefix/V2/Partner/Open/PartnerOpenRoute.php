<?php namespace App\Http\Route\Prefix\V2\Partner\Open;


class PartnerOpenRoute
{
    public function set($api)
    {
        $api->get('performance-faqs', 'FaqController@getPartnerPerformanceFaqs');
        $api->get('welcome', 'Auth\PartnerRegistrationController@getWelcomeMessage');
        $api->group(['prefix' => '{partner}'], function ($api) {
            $api->get('/', 'PartnerController@show');
            $api->get('locations', 'PartnerController@getLocations');
            $api->get('locations/all', 'LocationController@getPartnerServiceLocations');
            $api->get('categories', 'PartnerController@getCategories');
            $api->get('categories/{category}/services', 'PartnerController@getServices');
            $api->get('categories/{category}/addable-services', 'PartnerController@getAddableServices');
        });
        $api->get('rewards/faqs', 'Partner\PartnerRewardController@getFaqs');
    }
}