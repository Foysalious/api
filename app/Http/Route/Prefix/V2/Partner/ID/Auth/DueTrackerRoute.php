<?php namespace App\Http\Route\Prefix\V2\Partner\ID\Auth;
class DueTrackerRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'due-tracker'], function ($api) {
            $api->get('/due-list', 'Pos\\DueTrackerController@dueList');
            $api->get('/due-list/{customer_id}', 'Pos\\DueTrackerController@dueListByProfile');
            $api->post('/store/{customer_id}', 'Pos\\DueTrackerController@store');
            $api->post('/update/{customer_id}', 'Pos\\DueTrackerController@update');
            $api->post('/set-due-date-reminder/{customer_id}', 'Pos\\DueTrackerController@setDueDateReminder');
            $api->get('/due-datewise-customer-list', 'Pos\\DueTrackerController@dueDateWiseCustomerList');
            $api->delete('/entries/{entry_id}', 'Pos\\DueTrackerController@delete');
            $api->post('/send-sms/{customer_id}', 'Pos\\DueTrackerController@sendSMS');
            $api->get('/faqs', 'Pos\\DueTrackerController@getFaqs');
        });
        $api->delete('/customers/{customer}', 'Pos\\CustomerController@delete');
    }
}
