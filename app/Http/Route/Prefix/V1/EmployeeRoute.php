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
            $api->post('notifications/seen', 'Employee\NotificationController@seen');
            $api->group(['prefix' => 'supports'], function ($api) {
                $api->get('/', 'Employee\SupportController@index');
                $api->group(['prefix' => '{support}'], function ($api) {
                    $api->get('/', 'Employee\SupportController@show');
                    $api->post('feedback', 'Employee\SupportController@feedback');
                });
                $api->post('/', 'Employee\SupportController@store');
            });
            $api->group(['prefix' => 'announcements'], function ($api) {
                $api->get('/', 'Employee\AnnouncementController@index');
                $api->group(['prefix' => '{announcement}'], function ($api) {
                    $api->get('/', 'Employee\AnnouncementController@show');
                });
            });
        });
    }
}
