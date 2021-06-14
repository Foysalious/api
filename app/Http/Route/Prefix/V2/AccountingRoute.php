<?php namespace App\Http\Route\Prefix\V2;

class AccountingRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'accounting', 'middleware' => ['accounting.auth']], function ($api) {
            $api->post('/transfer', 'Accounting\\AccountingController@storeAccountsTransfer');
            $api->post('/transfer/{transfer_id}', 'Accounting\\AccountingController@updateAccountsTransfer');
            $api->post('/expense', 'Accounting\\IncomeExpenseController@storeExpenseEntry');
            $api->post('/expense/{expense_id}', 'Accounting\\IncomeExpenseController@updateExpenseEntry');
            $api->post('/income', 'Accounting\\IncomeExpenseController@storeIncomeEntry');
            $api->post('/income/{income_id}', 'Accounting\\IncomeExpenseController@updateIncomeEntry');
            $api->get('/income-expense-total', 'Accounting\\IncomeExpenseController@getTotalIncomeExpense');
            $api->get('/account-types', 'Accounting\\AccountController@getAccountTypeList');
            $api->get('/accounts', 'Accounting\\AccountController@getAccountList');
            $api->get('/cash-accounts', 'Accounting\\AccountController@getCashAccountList');
            $api->post('/accounts', 'Accounting\\AccountController@createAccount');
            $api->put('/accounts/{id}', 'Accounting\\AccountController@updateAccount');
            $api->delete('/accounts/{id}', 'Accounting\\AccountController@deleteAccount');
            $api->get('/icons', 'Accounting\\IconsController@getIcons');
            $api->group(['prefix' => 'due-tracker'], function ($api) {
                $api->get('/due-list', 'Accounting\\DueTrackerController@dueList');
                $api->get('/due-list/{customerId}', 'Accounting\\DueTrackerController@dueListByCustomerId');
                $api->post('/', 'Accounting\\DueTrackerController@store');
                $api->post('/{entry_id}', 'Accounting\\DueTrackerController@update');
                $api->delete('/{entry_id}', 'Accounting\\DueTrackerController@delete');
            });
            $api->group(['prefix' => 'home'], function ($api) {
                $api->get('/asset-balance', 'Accounting\\HomepageController@getAssetAccountBalance');
                $api->get('/income-expense-balance', 'Accounting\\HomepageController@getIncomeExpenseBalance');
                $api->get('income-expense-entries', 'Accounting\\HomepageController@getIncomeExpenseEntries');
                $api->get('due-collection-balance', 'Accounting\\HomepageController@getDueCollectionBalance');
                $api->get('account-list-balance', 'Accounting\\HomepageController@getAccountListBalance');
                $api->get('cash-accounts-entries/{accountKey}', 'Accounting\\HomepageController@getEntriesByAccountKey');
                $api->get('time-filter', 'Accounting\\HomepageController@getTimeFilters');
                $api->get('training-video', 'Accounting\\HomepageController@getTrainingVideo');
            });
            $api->group(['prefix' => 'entries'], function ($api) {
                $api->get('/{entry_id}', 'Accounting\\DueTrackerController@details');
            });
            $api->group(['prefix' => 'reports'], function ($api) {
                $api->get('/pos/customer-wise', 'Accounting\\ReportsController@getCustomerWiseReport');
                $api->get('/{reportType}', 'Accounting\\ReportsController@getAccountingReport');
                $api->get('/', 'Accounting\\ReportsController@getAccountingReportsList');
            });
        });
    }
}