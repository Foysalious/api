<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;


class LoanRoute
{

    public function set($api)
    {
        $api->group(['prefix' => 'loans', 'middleware' => 'shebaServer'], function ($api) {
            $api->get('/{loan_id}/admin/generate-pdf', 'Loan\\LoanController@generateApplication');
        });

        $api->group(['prefix' => 'bank', 'middleware' => 'jwtGlobalAuth'], function ($api) {
            $api->post('/password/reset', 'Auth\PasswordController@resetPasswordForBank');
        });

        $api->group(['prefix' => 'loans', 'middleware' => 'jwtGlobalAuth'], function ($api) {
            $api->post('/upload-retailer-list', 'Loan\\LoanController@uploadRetailerList');
            $api->get('/dashboard', 'Loan\\LoanController@getDashboardData');
            $api->post('/strategic-partner-dashboard', 'Loan\\LoanController@strategicPartnerDashboard');
            $api->get('/', 'Loan\\LoanController@index');
            $api->post('/from-portal', 'Loan\\LoanController@storeFromPortals');
            $api->get('/{loan_id}/details', 'Loan\\LoanController@show');
            $api->get('/{loan_id}/claim-list', 'Loan\\ClaimController@claimListForPortal');
            $api->get('/{loan_id}/repayment-list', 'Loan\\RepaymentController@repaymentListForPortal');
            $api->post('/{loan_id}', 'Loan\\LoanController@update');
            /* $api->post('/{loan_id}/upload-documents','Loan\\LoanController@uploadDocuments');*/
            $api->post('/{loan_id}/status', 'Loan\\LoanController@statusChange');
            $api->get('/{loan_id}/banks/{bank_id}', 'Loan\\LoanController@assignBank');
            $api->get('{partner_bank_loan}/logs', 'Loan\\LoanController@getChangeLogs');
            $api->post('{partner_bank_loan}/send-sms', 'Loan\\LoanController@sendSMS');
            $api->post('/{partner_bank_loan}/comments', 'Loan\\LoanController@storeComment');
            $api->get('/{partner_bank_loan}/comments', 'Loan\\LoanController@getComments');
            $api->post('/{partner_bank_loan}/status-change', 'Loan\\LoanController@statusChange');
            $api->get('/statuses', 'Loan\\LoanController@getStatus');
            $api->get('/{loan_id}/details-for-agents', 'Loan\\LoanController@showForAgent');
            $api->get('{partner_bank_loan}/logs-for-agent', 'Loan\\LoanController@getChangeLogsForAgent');
            $api->delete('{partner_bank_loan}/delete-documents', 'Loan\\LoanController@deleteDocument');
            $api->post('/{loan_id}/update-claim-status', 'Loan\\ClaimController@claimStatusUpdate');
            $api->get('loan-disbursement-report', 'Loan\\LoanReportController@loanDisbursementReport');
            $api->get('ipdc-sms-sending-report', 'Loan\\LoanReportController@ipdcSmsSendingReport');
            $api->get('loan-due-report', 'Loan\\LoanReportController@loanDueReport');
            $api->get('loan-status-report', 'Loan\\LoanReportController@loanStatusReport');
            $api->get('retailer-registration-report', 'Loan\\LoanReportController@retailerRegistrationReport');
            $api->get('/{loan_id}/generate-pdf', 'Loan\\LoanController@generateApplication');
            $api->get('/{loan_id}/download-documents', 'Loan\\LoanController@downloadDocuments');
            $api->post('/{loan_id}/upload-documents', 'Loan\\LoanController@uploadDocuments');

        });
    }

    public function indexed($api)
    {
        $api->group(['prefix' => 'loans'], function ($api) {
            $api->group(['prefix' => 'v2', 'middleware' => 'loan.version'], function ($api) {
                $api->post('/', 'Loan\\LoanController@store');
                $api->get('/personal-info', 'Loan\\LoanController@getPersonalInformation');
                $api->post('/personal-info', 'Loan\\LoanController@updatePersonalInformation');
                $api->get('/business-info', 'Loan\\LoanController@getBusinessInformation');
                $api->post('/business-info', 'Loan\\LoanController@updateBusinessInformation');
                $api->get('/finance-info', 'Loan\\LoanController@getFinanceInformation');
                $api->post('/finance-info', 'Loan\\LoanController@updateFinanceInformation');
                $api->get('/nominee-info', 'Loan\\LoanController@getNomineeInformation');
                $api->post('/nominee-grantor-info', 'Loan\\LoanController@updateNomineeGranterInformation');
                $api->get('/documents', 'Loan\\LoanController@getDocuments');
                $api->post('/documents', 'Loan\\LoanController@updateDocuments');
                $api->post('pictures', 'Loan\\LoanController@updateProfilePictures');
                $api->post('bank-statement', 'Loan\\LoanController@updateBankStatement');
                $api->post('trade-license', 'Loan\\LoanController@updateTradeLicense');
                $api->post('/proof-of-photograph', 'Loan\\LoanV2Controller@updateProfOfBusinessPhoto');
                $api->get('/information-completion', 'Loan\\LoanCompletionController@getLoanInformationCompletion');
                $api->get('/homepage', 'Loan\\LoanController@getHomepage');
                $api->get('/bank-interest', 'Loan\\LoanController@getBankInterest');
                $api->get('/history', 'Loan\\LoanController@history');
            });

            $api->get('/{loan_id}/account-info', 'Loan\\LoanController@accountInfo');
            $api->post('/{loan_id}/claim', 'Loan\\ClaimController@claim');
            $api->get('/{loan_id}/claim-list', 'Loan\\ClaimController@claimList');
            $api->post('/{loan_id}/claim-approval-msg-seen', 'Loan\\ClaimController@approvedClaimMsgSeen');
            $api->get('/{loan_id}/repayment-list', 'Loan\\RepaymentController@repaymentList');
            $api->post('/{loan_id}/repayment-from-wallet', 'Loan\\RepaymentController@repaymentFromWallet');
            $api->post('/{loan_id}/pay', 'Loan\\RepaymentController@init');
        });
    }
}
