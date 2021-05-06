<?php namespace App\Http\Route\Prefix\V2;

class AccountingRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'accounting', 'middleware' => ['accounting.auth']], function ($api) {
            $api->post('/transfer', 'Accounting\\AccountingController@storeAccountsTransfer');
            $api->post('/expense', 'Accounting\\IncomeExpenseController@storeExpenseEntry');
            $api->post('/income', 'Accounting\\IncomeExpenseController@storeIncomeEntry');
            $api->get('/account-types', 'Accounting\\AccountController@getAccountTypeList');
            $api->get('/accounts', 'Accounting\\AccountController@getAccountList');
            $api->get('/cash-accounts', 'Accounting\\AccountController@getCashAccountList');
            $api->post('/accounts', 'Accounting\\AccountController@createAccount');
            $api->put('/accounts/{id}', 'Accounting\\AccountController@updateAccount');
            $api->delete('/accounts/{id}', 'Accounting\\AccountController@deleteAccount');
            $api->get('/icons', 'Accounting\\IconsController@getIcons');
            $api->group(['prefix' => 'due-tracker'], function ($api) {
                $api->post('/', 'Accounting\\DueTrackerController@store');
                $api->post('/{entry_id}', 'Accounting\\DueTrackerController@update');
                $api->delete('/{entry_id}', 'Accounting\\DueTrackerController@delete');
            });
            $api->group(['prefix' => 'home'], function ($api) {
                $api->get('/asset-balance', 'Accounting\\HomepageController@getAssetAccountBalance');
                $api->get('/income-expense-balance', 'Accounting\\HomepageController@getIncomeExpenseBalance');
                $api->get('income-expense-entries', 'Accounting\\HomepageController@getIncomeExpenseEntries');
            });
        });
    }
}