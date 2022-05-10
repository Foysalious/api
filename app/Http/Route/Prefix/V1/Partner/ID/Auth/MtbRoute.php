<?php

namespace App\Http\Route\Prefix\V1\Partner\ID\Auth;

class MtbRoute
{
    public function set($api)
    {
        $api->group(['middleware' => ['accessToken']], function ($api) {
            $api->post('/partners/mtb-apply', 'Mtb\MtbController@apply');
            $api->post('/partners/mtb-send-otp', 'Mtb\MtbController@sendOtp');
        });
    }
}
