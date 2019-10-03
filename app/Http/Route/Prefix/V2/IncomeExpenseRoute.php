<?php namespace App\Http\Route\Prefix\V2;

class IncomeExpenseRoute
{
    public function set($api)
    {
        $api->get('income-expense', 'Partner\IncomeExpenseController@index');
        $api->group(['prefix' => 'incomes'], function ($api) {
            $api->get('/', 'Partner\IncomeController@index');
            $api->group(['prefix' => '{income}'], function ($api) {
                $api->get('/', 'Partner\IncomeController@show');
            });
        });

        $api->group(['prefix' => 'expense'], function ($api) {
            $api->get('/', 'Partner\ExpenseController@index');
        });
    }
}
