<?php namespace App\Http\Route\Prefix\V1;

class EmployeeRoute
{
    public function set($api)
    {
        $api->post('employee/login', 'Employee\EmployeeController@login');
        $api->group(['prefix' => 'employee', 'middleware' => ['jwtAuth']], function ($api) {

            $api->group(['prefix' => 'me'], function ($api) {
                $api->get('/', 'Employee\EmployeeController@me');
                $api->post('/', 'Employee\EmployeeController@updateMe');
                $api->post('basic', 'Employee\EmployeeController@updateBasicInformation');
            });
            $api->group(['prefix' => 'payroll'], function ($api) {
                $api->get('payslip', 'Employee\PayrollController@downloadPayslip');
                $api->get('disbursed-month', 'Employee\PayrollController@disbursedMonth');
            });
            $api->group(['prefix' => 'profile'], function ($api) {
                $api->group(['prefix' => '{business_member}'], function ($api) {
                    $api->get('financial', 'Employee\EmployeeController@getFinancialInfo');
                    $api->get('official', 'Employee\EmployeeController@getOfficialInfo');
                    $api->post('official', 'Employee\EmployeeController@updateOfficialInfo');
                    $api->post('update', 'Employee\EmployeeController@updateEmployee');
                    $api->post('emergency', 'Employee\EmployeeController@updateEmergencyInfo');
                    $api->get('emergency', 'Employee\EmployeeController@getEmergencyContactInfo');
                    $api->get('personal', 'Employee\EmployeeController@getPersonalInfo');
                    $api->post('personal', 'Employee\EmployeeController@updatePersonalInfo');
                });
            });
            $api->get('subordinate-employee-list', 'Employee\VisitController@getManagerSubordinateEmployeeList');
            $api->group(['prefix' => 'employee-visit'], function ($api) {
                $api->post('create', 'Employee\VisitController@create');
                $api->post('update/{visit_id}', 'Employee\VisitController@update');
                $api->get('own-ongoing-visits', 'Employee\VisitController@ownOngoingVisits');
                $api->get('own-visit-history', 'Employee\VisitController@ownVisitHistory');
                $api->get('team-visits', 'Employee\VisitController@teamVisitsList');
                $api->group(['prefix' => '{visit_id}'], function ($api) {
                    $api->get('/', 'Employee\VisitController@show');
                    $api->post('note', 'Employee\VisitController@storeNote');
                    $api->post('photo', 'Employee\VisitController@storePhoto');
                    $api->post('status-update', 'Employee\VisitController@updateStatus');
                });
            });
            $api->group(['prefix' => 'my-team'], function ($api) {
                $api->get('/', 'Employee\MyTeamController@myTeam');
                $api->get('{employee_id}', 'Employee\MyTeamController@employeeDetails');
            });
            //$api->post('password', 'Employee\EmployeeController@updateMyPassword');
            $api->get('dashboard', 'Employee\EmployeeController@getDashboard');
            $api->get('dashboard-menu', 'Employee\DashboardController@index');
            $api->get('notifications', 'Employee\NotificationController@index');
            $api->get('last-notifications', 'Employee\NotificationController@lastNotificationCount');
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
                $api->get('/info', 'Employee\AttendanceController@attendanceInfo');
                $api->post('action', 'Employee\AttendanceController@takeAction');
                $api->get('today', 'Employee\AttendanceController@getTodaysInfo');
                $api->post('note', 'Employee\AttendanceController@storeNote');
            });
            $api->group(['prefix' => 'leaves'], function ($api) {
                $api->get('/', 'Employee\LeaveController@index');
                $api->get('/dates', 'Employee\LeaveController@getLeaveDates');
                $api->get('/types', 'Employee\LeaveController@getLeaveTypes');
                $api->get('/settings', 'Employee\LeaveController@getLeaveSettings');
                $api->post('/', 'Employee\LeaveController@store');
                $api->get('/reject-reasons', 'Employee\LeaveController@rejectReasons');
                $api->group(['prefix' => '{leave}'], function ($api) {
                    $api->get('/', 'Employee\LeaveController@show');
                    $api->post('/', 'Employee\LeaveController@updateStatus');
                    $api->post('update', 'Employee\LeaveController@update');
                    $api->post('cancel', 'Employee\LeaveController@cancel');
                });
            });
            $api->group(['prefix' => 'approval-requests'], function ($api) {
                $api->get('/', 'Employee\ApprovalRequestController@index');
                $api->get('/leaves/{business_member}', 'Employee\ApprovalRequestController@leaveHistory');
                $api->get('/{approval_request}', 'Employee\ApprovalRequestController@show');
                $api->post('/status', 'Employee\ApprovalRequestController@updateStatus');
            });
            $api->group(['prefix' => 'holidays'], function ($api) {
                $api->get('/', 'Employee\HolidayController@getHolidays');
            });
            $api->group(['prefix' => 'departments'], function ($api) {
                $api->get('/', 'Employee\DepartmentController@index');
            });
            $api->group(['prefix' => 'designations'], function ($api) {
                $api->get('/', 'Employee\DesignationController@index');
            });
            $api->get('managers', 'Employee\EmployeeController@getManagersList');
            $api->get('/', 'Employee\EmployeeController@index');
            $api->get('/{employee}', 'Employee\EmployeeController@show');
        });
    }
}
