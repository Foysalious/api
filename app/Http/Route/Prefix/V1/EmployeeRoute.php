<?php namespace App\Http\Route\Prefix\V1;

class EmployeeRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'employee', 'middleware' => ['jwtAuth']], function ($api) {
            $api->get('me', 'Employee\EmployeeController@me');
            $api->post('me', 'Employee\EmployeeController@updateMe');
            $api->post('password', 'Employee\EmployeeController@updateMyPassword');
            $api->get('dashboard', 'Employee\EmployeeController@getDashboard');
            $api->get('notifications', 'Employee\NotificationController@index');
            $api->get('test-notification', 'Employee\NotificationController@test');
            $api->post('notifications/seen', 'Employee\NotificationController@seen');
            $api->group(['prefix' => 'supports'], function ($api) {
                $api->get('/', 'Employee\SupportController@index');
                $api->group(['prefix' => '{support}'], function ($api) {
                    $api->get('/', 'Employee\SupportController@show');
                    $api->post('feedback', 'Employee\SupportController@feedback');
                });
                $api->post('/', 'Employee\SupportController@store');
            });
            $api->group(['prefix' => 'expense'], function ($api) {
                $api->get('/', 'Employee\ExpenseController@index');
                $api->get('/download-pdf', 'Employee\ExpenseController@downloadPdf');
                $api->group(['prefix' => '{expense}'], function ($api) {
                    $api->get('/', 'Employee\ExpenseController@show');
                    $api->post('/', 'Employee\ExpenseController@update');
                    $api->delete('/', 'Employee\ExpenseController@delete');
                    $api->delete('attachments/{attachment}', 'Employee\ExpenseController@deleteAttachment');
                });
                $api->post('/', 'Employee\ExpenseController@store');
            });
            $api->group(['prefix' => 'announcements'], function ($api) {
                $api->get('/', 'Employee\AnnouncementController@index');
                $api->group(['prefix' => '{announcement}'], function ($api) {
                    $api->get('/', 'Employee\AnnouncementController@show');
                });
            });
            $api->group(['prefix' => 'attendances'], function ($api) {
                $api->get('/', 'Employee\AttendanceController@index');
                $api->post('action', 'Employee\AttendanceController@takeAction');
                $api->get('today', 'Employee\AttendanceController@getTodaysInfo');
            });
            $api->group(['prefix' => 'leaves'], function ($api) {
                $api->get('/', 'Employee\LeaveController@index');
                $api->get('/types', 'Employee\LeaveController@getLeaveTypes');
                $api->post('/', 'Employee\LeaveController@store');
                $api->group(['prefix' => '{leave}'], function ($api) {
                    $api->get('/', 'Employee\LeaveController@show');
                    $api->post('/', 'Employee\LeaveController@updateStatus');
                });
            });
            $api->group(['prefix' => 'approval-requests'], function ($api) {
                $api->post('/status', 'Employee\ApprovalRequestController@updateStatus');
                $api->get('/{approval_request}', 'Employee\ApprovalRequestController@show');
                $api->get('/', 'Employee\ApprovalRequestController@index');
            });
            $api->group(['prefix' => 'holidays'], function ($api) {
                $api->get('/', 'Employee\HolidayController@getHolidays');
            });
        });
    }
}
