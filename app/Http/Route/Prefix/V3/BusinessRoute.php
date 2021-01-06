<?php namespace App\Http\Route\Prefix\V3;

class BusinessRoute
{
    public function set($api)
    {
        $api->get('test-mail', 'B2b\TestMailsController@testMail');
        $api->post('business/register', 'B2b\RegistrationController@registerV3')->middleware('jwtAuth');
        $api->post('business/email-verify', 'Profile\ProfileController@verifyEmailWithVerificationCode')->middleware('jwtAuth');
        $api->get('business/send-verification-code', 'Profile\ProfileController@sendEmailVerificationCode')->middleware('jwtAuth');
        $api->get('business/send-verification-link', 'Profile\ProfileController@sendEmailVerificationlink')->middleware('jwtAuth');
        $api->group(['prefix' => 'businesses', 'middleware' => ['business.auth']], function ($api) {
            $api->group(['prefix' => '{business}'], function ($api) {
                $api->get('vendors', 'B2b\BusinessesController@getVendorsListV3');
                $api->group(['prefix' => 'approval-settings'], function ($api) {
                    $api->get('/', 'B2b\ApprovalSettingsController@index');
                    $api->post('/', 'B2b\ApprovalSettingsController@store');
                    $api->group(['prefix' => '{setting}'], function ($api) {
                        $api->get('/', 'B2b\ApprovalSettingsController@show');
                        $api->post('/', 'B2b\ApprovalSettingsController@update');
                        $api->delete('/', 'B2b\ApprovalSettingsController@delete');
                    });
                });
            });
        });
    }
}
