<?php namespace App\Http\Route\Prefix\V2\Partner;
class ReferralRoute
{
    private $namespace = 'Referral';

    public function globals($api)
    {
        $api->group(['prefix' => 'referrals'], function ($api) {
            $api->get('faqs', "$this->namespace\\PartnerReferralController@getReferralFaqs");
            $api->get('steps', "$this->namespace\\PartnerReferralController@getReferralSteps");
        });
    }

    public function individuals($api)
    {
        $api->group(['prefix' => 'referrals'], function ($api) {
            $api->get('/home',"$this->namespace\\PartnerReferralController@home");
            $api->get('/', "$this->namespace\\PartnerReferralController@index");
            $api->post('/', "$this->namespace\\PartnerReferralController@store");
            $api->get('/{referral}',"$this->namespace\\PartnerReferralController@show");
        });
    }
}
