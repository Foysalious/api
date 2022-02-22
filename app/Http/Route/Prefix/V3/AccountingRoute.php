<?php namespace App\Http\Route\Prefix\V3;

class AccountingRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'accounting', 'middleware' => ['accounting.auth']], function ($api) {
            $api->group(['prefix' => 'due-tracker'], function ($api) {
                $api->get('/balance', 'Accounting\\DueTrackerControllerV2@getDueTrackerBalance');
                $api->get('/search-due-list', 'Accounting\\DueTrackerControllerV2@searchDueList');
            });
        });
    }
}