<?php namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class IndexRoute
{
    public function set($api)
    {
        $api->group(['prefix' => '{partner}', 'middleware' => ['manager.auth']], function ($api) {});
    }
}
