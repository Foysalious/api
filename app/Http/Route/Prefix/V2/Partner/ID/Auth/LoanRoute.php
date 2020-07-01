<?php


namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;


class LoanRoute
{
    function set($api){
        $api->group(['prefix'=>'bank', 'middleware'=>'jwtGlobalAuth'],function($api){
            $api->post('/password/reset','Auth\PasswordController@resetPasswordForBank');
        });

        $api->group(['prefix'=>'loans','middleware'=>'jwtGlobalAuth'], function ($api) {
            $api->post('/upload-retailer-list','LoanController@uploadRetailerList');
            $api->post('/strategic-partner-dashboard','LoanController@strategicPartnerDashboard');
            $api->get('/', 'LoanController@index');
            $api->post('/from-portal','LoanController@storeFromPortals');
            $api->get('/{loan_id}/details','LoanController@show');
            $api->post('/{loan_id}','LoanController@update');
            $api->get('/{loan_id}/download-documents','LoanController@downloadDocuments');
            /* $api->post('/{loan_id}/upload-documents','LoanController@uploadDocuments');*/
            $api->post('/{loan_id}/status','LoanController@statusChange');
            $api->get('/{loan_id}/banks/{bank_id}', 'LoanController@assignBank');
            $api->get('{partner_bank_loan}/logs', 'LoanController@getChangeLogs');
            $api->post('{partner_bank_loan}/send-sms', 'LoanController@sendSMS');
            $api->post('/{partner_bank_loan}/comments', 'LoanController@storeComment');
            $api->get('/{partner_bank_loan}/comments', 'LoanController@getComments');
            $api->post('/{partner_bank_loan}/status-change', 'LoanController@statusChange');
            $api->get('/{loan_id}/generate-pdf','LoanController@generateApplication');
            $api->get('/statuses','LoanController@getStatus');
            $api->get('/{loan_id}/details-for-agents','LoanController@showForAgent');
            $api->get('{partner_bank_loan}/logs-for-agent', 'LoanController@getChangeLogsForAgent');
            $api->delete('{partner_bank_loan}/delete-documents', 'LoanController@deleteDocument');
            $api->post('/{loan_id}/upload-documents','LoanController@uploadDocuments');
        });
    }
}
