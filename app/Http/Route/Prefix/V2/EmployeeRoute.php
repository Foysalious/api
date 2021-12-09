<?php namespace App\Http\Route\Prefix\V2;

class EmployeeRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'employee', 'middleware' => ['jwtAuth']], function ($api) {
            $api->group(['prefix' => 'employee-visit'], function ($api) {
                $api->get('own-visit-history', 'Employee\VisitController@ownVisitHistoryV2');
                $api->get('team-visits', 'Employee\VisitController@teamVisitsListV2');
            });
        });
    }
}