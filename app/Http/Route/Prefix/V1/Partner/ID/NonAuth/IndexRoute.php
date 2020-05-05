<?php namespace App\Http\Route\Prefix\V1\Partner\ID\NonAuth;

class IndexRoute
{
    public function set($api)
    {
        $api->group(['prefix' => '{partner}'], function ($api) {});
    }
}
