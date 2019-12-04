<?php namespace App\Http\Route\Prefix\V2\Partner;

use App\Http\Route\Prefix\V2\Partner\ID\NonAuth\IndexRoute as IDNonAuthRoute;
use App\Http\Route\Prefix\V2\Partner\ID\Auth\IndexRoute as IDAuthRoute;
use App\Http\Route\Prefix\V2\Partner\PosRoute as PosRoute;

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
        });
        $api->group(['prefix'     => 'loans',
                     'middleware' => 'jwtGlobalAuth'
        ], function ($api) {
            $api->get('/', 'SpLoanController@index');
            $api->get('/{loan_id}/details','SpLoanController@show');
            $api->post('/{loan_id}','SpLoanController@update');
            $api->post('/{loan_id}/upload-documents','SpLoanController@uploadDocuments');
            $api->post('/{loan_id}/status','SpLoanController@statusChange');
            $api->get('/{loan_id}/banks/{bank_id}', 'SpLoanController@assignBank');
            $api->get('{partner_bank_loan}/logs', 'SpLoanController@getChangeLogs');
            $api->post('{partner_bank_loan}/send-sms', 'SpLoanController@sendSMS');
            $api->post('/{partner_bank_loan}/comments', 'SpLoanController@storeComment');
            $api->get('/{partner_bank_loan}/comments', 'SpLoanController@getComments');
            $api->post('/{partner_bank_loan}/status-change', 'SpLoanController@statusChange');
        });
    }
}
