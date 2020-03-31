<?php namespace App\Http\Route\Prefix\V2\Resource;


class AuthRoute
{
    public function set($api)
    {
        $api->group(['middleware' => 'resource.jwt.auth'], function ($api) {
            $api->get('profile', 'Resource\ResourceController@getProfile');
            $api->get('home', 'Resource\ResourceController@getHome');
            $api->group(['prefix' => 'jobs'], function ($api) {
                $api->get('/', 'Resource\ResourceJobController@index');
                $api->group(['prefix' => '{job}'], function ($api) {
                    $api->get('schedules', 'Resource\ResourceController@getSchedules');
                    $api->get('/', 'Resource\ResourceJobController@orderDetails');
                });
            });
        });
    }
}