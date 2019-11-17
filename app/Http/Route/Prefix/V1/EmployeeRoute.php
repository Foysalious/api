<?php namespace App\Http\Route\Prefix\V1;


class EmployeeRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'employee'], function ($api) {
            $api->get('dashboard', 'Employee\EmployeeController@getDashboard')->middleware('jwtAuth');
        });
    }
}