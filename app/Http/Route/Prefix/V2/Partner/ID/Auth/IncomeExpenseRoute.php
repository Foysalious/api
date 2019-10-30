<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;

class IncomeExpenseRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'income-expense'], function ($api) {
            $api->get('/', 'Partner\IncomeExpenseController@index');
            $api->get('/payables', 'Partner\PayableController@index');
            $api->get('/payables/{payable}', 'Partner\PayableController@show');
            $api->post('/payables/{payable}/pay', 'Partner\PayableController@pay');
            $api->get('/payables/{payable}/logs', 'Partner\PayableController@logs');
            $api->get('/receivables', 'Partner\IncomeExpenseController@receivable');
            $api->get('/heads', 'Partner\IncomeExpenseController@getHeads');
        });
        $api->group(['prefix' => 'incomes'], function ($api) {
            $api->get('/', 'Partner\IncomeController@index');
            $api->post('/', 'Partner\IncomeController@store');
            $api->post('/{incomeId}', 'Partner\IncomeController@update');
            $api->group(['prefix' => '{income}'], function ($api) {
                $api->get('/', 'Partner\IncomeController@show');
            });
        });
        $api->group(['prefix' => 'expenses'], function ($api) {
            $api->get('/', 'Partner\ExpenseController@index');
            $api->post('/', 'Partner\ExpenseController@store');
            $api->group(['prefix' => '{expense}'], function ($api) {
                $api->get('/', 'Partner\ExpenseController@show');
                $api->post('/', 'Partner\ExpenseController@update');
            });
        });
    }
}
