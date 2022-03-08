<?php namespace App\Http\Route\Prefix\V3;

class AccountingRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'accounting', 'middleware' => ['accounting.auth']], function ($api) {
            $api->group(['prefix' => 'due-tracker'], function ($api) {
                $api->get('/due-list-balance', 'Accounting\\DueTrackerControllerV2@getDueListBalance');
                $api->get('/search-due-list', 'Accounting\\DueTrackerControllerV2@searchDueList');
                $api->get('/download-pdf', 'Accounting\\DueTrackerControllerV2@downloadPdf');
                $api->get('/due-list', 'Accounting\\DueTrackerControllerV2@dueList');
                $api->get('/due-list/{customerId}', 'Accounting\\DueTrackerControllerV2@dueList');
                $api->get('/due-list/{customerId}/balance', 'Accounting\\DueTrackerControllerV2@dueListBalanceByCustomer');

            });
        });
    }
}