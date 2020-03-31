<?php namespace App\Http\Route\Prefix\V2\Resource;


class AuthRoute
{
    public function set($api)
    {
        $api->group(['middleware' => 'resource.jwt.auth'], function ($api) {
            $api->get('profile', 'Resource\ResourceController@getProfile');
            $api->get('job/{job}/schedules', 'Resource\ResourceController@getSchedules');
            $api->get('jobs', 'Resource\ResourceJobController@index');
            $api->get('home', 'Resource\ResourceController@getHome');
        });
    }
}