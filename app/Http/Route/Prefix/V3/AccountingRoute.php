<?php namespace App\Http\Route\Prefix\V3;

class AccountingRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'accounting', 'middleware' => ['accounting.auth']], function ($api) {
            $api->group(['prefix' => 'due-tracker'], function ($api) {
                $api->post('/', 'Accounting\\DueTrackerControllerV2@store');
                $api->get('/due-list-balance', 'Accounting\\DueTrackerControllerV2@getDueListBalance');
                $api->get('/due-list', 'Accounting\\DueTrackerControllerV2@dueList');
                $api->get('/due-list/{contactId}/balance', 'Accounting\\DueTrackerControllerV2@dueListBalanceByContact');
                $api->get('/due-list/{contactId}', 'Accounting\\DueTrackerControllerV2@dueListByContact');
                $api->get('/download-pdf', 'Accounting\\DueTrackerControllerV2@downloadPdf');
                $api->get('/report','Accounting\\DueTrackerControllerV2@getReport');
                $api->get('/report/{contactId}','Accounting\\DueTrackerControllerV2@getReport');
                $api->post('/bad-debts', 'Accounting\\DueTrackerControllerV2@badDebts');
                $api->get('/sms-content/','Accounting\\DueTrackerSmsController@getSmsContent');
                $api->post('/send-single-sms/','Accounting\\DueTrackerSmsController@sendSingleSmsToContact');
                $api->post('/send-bulk-sms/','Accounting\\DueTrackerSmsController@sendBulkSmsToContacts');
                $api->get('/bulk-sms-eligible-list/','Accounting\\DueTrackerSmsController@getBulkSmsContactList');

                $api->group(['prefix' => 'reminders'], function ($api) {
                    $api->get('/', 'Accounting\\DueTrackerReminderController@reminders');
                    $api->post('/', 'Accounting\\DueTrackerReminderController@store');
                    $api->put('/{reminder_id}', 'Accounting\\DueTrackerReminderController@update');
                    $api->delete('/{reminder_id}', 'Accounting\\DueTrackerReminderController@delete');
                });
            });
            $api->group(['prefix' => 'entries'], function ($api) {
                $api->get('/{entry_id}', 'Accounting\\EntriesControllerV2@details');
                $api->delete('/{entry_id}', 'Accounting\\EntriesControllerV2@delete');
            });
        });
        $api->group(['prefix' => 'accounting'], function ($api) {
            $api->group(['prefix' => 'due-tracker'], function ($api) {
                $api->get('/public-report', 'Accounting\\DueTrackerControllerV2@publicReport');
                $api->post('/reminder/notification', 'Accounting\\DueTrackerReminderController@reminderNotificationWebhook');
            });
        });
    }
}