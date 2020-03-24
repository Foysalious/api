<?php namespace App\Http\Route\Prefix\V1\Resource;

use App\Http\Route\Prefix\V1\Resource\ID\Auth\IndexRoute as IDAuthRoute;
use App\Http\Route\Prefix\V1\Resource\ID\NonAuth\IndexRoute as IDNonAuthRoute;

class ResourceRoute
{
    public function set($api)
    {
        $api->group(['prefix' => 'resources'], function ($api) {
            (new IDAuthRoute())->set($api);
            (new IDNonAuthRoute())->set($api);
        });
    }
}