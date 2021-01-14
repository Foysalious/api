<?php namespace App\Http\Route\Prefix\V2;

class EmployeeRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'employee', 'middleware' => ['jwtAuth']], function ($api) {
            $api->group(['prefix' => 'leaves'], function ($api) {
                $api->post('/', 'Employee\LeaveV2Controller@store');
            });
        });
    }
}