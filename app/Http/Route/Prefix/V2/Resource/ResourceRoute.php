<?php namespace App\Http\Route\Prefix\V2\Resource;

class ResourceRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'resources'], function ($api) {
            (new AuthRoute())->set($api);
            (new NonAuthRoute())->set($api);
        });
    }
}