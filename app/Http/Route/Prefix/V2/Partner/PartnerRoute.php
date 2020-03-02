<?php namespace App\Http\Route\Prefix\V2\Partner;

use App\Http\Route\Prefix\V2\Partner\ID\Auth\IndexRoute as IDAuthRoute;
use App\Http\Route\Prefix\V2\Partner\ID\NonAuth\IndexRoute as IDNonAuthRoute;
use App\Http\Route\Prefix\V2\Partner\PosRoute as PosRoute;

class PartnerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'partners'], function ($api) {
            $api->get('performance-faqs', 'FaqController@getPartnerPerformanceFaqs');
            $api->get('welcome', 'Auth\PartnerRegistrationController@getWelcomeMessage');
            $api->get('rewards/faqs', 'Partner\PartnerRewardController@getFaqs');
            $api->get('referral/faqs', 'Partner\PartnerRewardController@getReferralFaqs');
            $api->get('referral/steps', 'Partner\PartnerRewardController@getReferralSteps');
            $api->get('resource-types', 'PartnerController@getResourceTypes');
            $api->get('subscriptions', 'Partner\PartnerSubscriptionController@getAllPackages');
            (new IDNonAuthRoute())->set($api);
            (new IDAuthRoute())->set($api);
            (new PosRoute())->set($api);
        });
        $api->group(['prefix'=>'bank', 'middleware'=>'jwtGlobalAuth'],function($api){
            $api->post('/password/reset','Auth\PasswordController@resetPasswordForBank');
        });
        $api->group(['prefix'=>'loans', 'middleware'=>'jwtGlobalAuth'], function ($api) {
            $api->get('/', 'LoanController@index');
            $api->post('/from-portal','LoanController@storeFromPortals');
            $api->get('/{loan_id}/details','LoanController@show');
            $api->post('/{loan_id}','LoanController@update');
            $api->get('/{loan_id}/download-documents','LoanController@downloadDocuments');
            $api->post('/{loan_id}/upload-documents','LoanController@uploadDocuments');
            $api->post('/{loan_id}/status','LoanController@statusChange');
            $api->get('/{loan_id}/banks/{bank_id}', 'LoanController@assignBank');
            $api->get('{partner_bank_loan}/logs', 'LoanController@getChangeLogs');
            $api->post('{partner_bank_loan}/send-sms', 'LoanController@sendSMS');
            $api->post('/{partner_bank_loan}/comments', 'LoanController@storeComment');
            $api->get('/{partner_bank_loan}/comments', 'LoanController@getComments');
            $api->post('/{partner_bank_loan}/status-change', 'LoanController@statusChange');
            $api->get('/{loan_id}/generate-pdf','LoanController@generateApplication');
            $api->get('/statuses','LoanController@getStatus');
        });
    }
}
