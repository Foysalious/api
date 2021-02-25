<?php namespace App\Http\Route\Prefix\V2;

class UserRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'user', 'middleware' => 'jwtGlobalAuth'], function ($api) {
            $api->get('/', 'UserController@show');
        });
    }
}
