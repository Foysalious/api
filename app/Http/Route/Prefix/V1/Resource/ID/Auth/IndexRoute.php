<?php namespace App\Http\Route\Prefix\V1\Resource\ID\Auth;


class IndexRoute
{
    public function set($api)
    {
        //TODO: Need to Add JWT Auth Middleware
        $api->group([], function ($api) {
            $api->get('profile', 'Resource\ResourceController@getProfile');
        });
    }
}