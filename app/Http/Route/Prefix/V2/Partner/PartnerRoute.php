<?php namespace App\Http\Route\Prefix\V2\Partner;


use App\Http\Route\Prefix\V2\Partner\ID\NonAuth\IndexRoute as IDNonAuthRoute;
use App\Http\Route\Prefix\V2\Partner\ID\Auth\IndexRoute as IDAuthRoute;

class PartnerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners'], function ($api) {
            (new IDNonAuthRoute())->set($api);
            (new IDAuthRoute())->set($api);
            $api->get('performance-faqs', 'FaqController@getPartnerPerformanceFaqs');
            $api->get('welcome', 'Auth\PartnerRegistrationController@getWelcomeMessage');
            $api->get('rewards/faqs', 'Partner\PartnerRewardController@getFaqs');
        });
    }
}