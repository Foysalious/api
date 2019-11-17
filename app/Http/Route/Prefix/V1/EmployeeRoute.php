<?php namespace App\Http\Route\Prefix\V1;


class EmployeeRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'employee', 'middleware' => ['jwtAuth']], function ($api) {
            $api->get('dashboard', 'Employee\EmployeeController@getDashboard');
            $api->group(['prefix' => 'supports'], function ($api) {
                $api->get('/', 'Employee\SupportController@index');
                $api->post('/', 'Employee\SupportController@store');
            });
        });
    }
}