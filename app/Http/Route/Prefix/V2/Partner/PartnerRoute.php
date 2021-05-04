<?php namespace App\Http\Route\Prefix\V2\Partner;

use App\Http\Route\Prefix\V2\Partner\ID\Auth\DeliveryRoute;
use App\Http\Route\Prefix\V2\Partner\ID\Auth\EmiRoute as EmiRoute;
use App\Http\Route\Prefix\V2\Partner\ID\Auth\IndexRoute as IDAuthRoute;
use App\Http\Route\Prefix\V2\Partner\ID\Auth\LoanRoute;
use App\Http\Route\Prefix\V2\Partner\ID\Auth\NeoBankingRoute;
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
            $api->get('business-types', 'PartnerController@getBusinessTypes');
            $api->get('subscriptions', 'Partner\PartnerSubscriptionController@getAllPackages');
            $api->post('notification-store', "NeoBanking\\NeoBankingController@sendNotification");
            $api->post('{partner}/account-number-store', "NeoBanking\\NeoBankingController@accountNumberStore");
            (new IDNonAuthRoute())->set($api);
            (new IDAuthRoute())->set($api);
            (new PosRoute())->set($api);
            (new ReferralRoute())->globals($api);
            (new EmiRoute())->set($api);
        });
        (new LoanRoute())->set($api);
        (new NeoBankingRoute())->set($api);
        (new DeliveryRoute())->set($api);

    }
}
