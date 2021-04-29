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
            $api->group(['prefix' => 'due-tracker'], function ($api) {
                $api->post('/{customer_id}', 'Accounting\\DueTrackerController@store');
                $api->post('update/{customer_id}', 'Accounting\\DueTrackerController@update');
                $api->delete('/{customer_id}', 'Accounting\\DueTrackerController@delete');
            });
        });
    }
}