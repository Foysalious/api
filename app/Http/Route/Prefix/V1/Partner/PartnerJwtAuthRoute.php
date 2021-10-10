<?php namespace App\Http\Route\Prefix\V1\Partner;


class PartnerJwtAuthRoute
{
    public function set($api)
    {
        $api->group(['middleware' => 'jwt.partner.auth'], function ($api) {
            $api->post('resources/{resource}/change-leave-status', 'PartnerController@changeLeaveStatusOfResource');
        });
    }
}