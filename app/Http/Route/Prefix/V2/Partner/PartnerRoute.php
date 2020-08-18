<?php namespace App\Http\Route\Prefix\V2\Partner;

use App\Http\Route\Prefix\V2\Partner\ID\Auth\EmiRoute as EmiRoute;
use App\Http\Route\Prefix\V2\Partner\ID\Auth\IndexRoute as IDAuthRoute;
use App\Http\Route\Prefix\V2\Partner\ID\Auth\LoanRoute;
use App\Http\Route\Prefix\V2\Partner\ID\NonAuth\IndexRoute as IDNonAuthRoute;
use App\Http\Route\Prefix\V2\Partner\PosRoute as PosRoute;
use App\Http\Route\Prefix\V2\Partner\ReferralRoute as ReferralRoute;

class PartnerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners'], function ($api) {
            $api->get('performance-faqs', 'FaqController@getPartnerPerformanceFaqs');
            $api->get('welcome', 'Auth\PartnerRegistrationController@getWelcomeMessage');
            $api->get('rewards/faqs', 'Partner\PartnerRewardController@getFaqs');
            $api->get('resource-types', 'PartnerController@getResourceTypes');
            $api->get('subscriptions', 'Partner\PartnerSubscriptionController@getAllPackages');
            (new IDNonAuthRoute())->set($api);
            (new IDAuthRoute())->set($api);
            (new PosRoute())->set($api);
            (new ReferralRoute())->globals($api);
            (new EmiRoute())->set($api);
        });
        (new LoanRoute())->set($api);
    }
}
