<?php namespace App\Http\Route\Prefix\V2;

class AccountingRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'accounting', 'middleware' => ['accounting.auth']], function ($api) {
            $api->post('/transfer', 'Accounting\\AccountingController@storeAccountsTransfer');
            $api->post('/expense', 'Accounting\\ExpenseController@storeExpenseEntry');
        });
    }
}