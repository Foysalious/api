<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;
class DueTrackerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'due-tracker'], function ($api) {
            $api->get('/due-list', 'Pos\\DueTrackerController@dueList');
            $api->get('/due-list/{customer_id}', 'Pos\\DueTrackerController@dueListByProfile');
            $api->post('/store/{customer_id}', 'Pos\\DueTrackerController@store');
            $api->post('/set-due-date-reminder/{customer_id}', 'Pos\\DueTrackerController@setDueDateReminder');
            $api->get('/due-datewise-customer-list', 'Pos\\DueTrackerController@dueDatewiseCustomerList');
        });
    }
}
