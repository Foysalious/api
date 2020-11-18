<?php namespace App\Http\Middleware\JWT;

use App\Http\Middleware\AccessTokenMiddleware;

use Sheba\OAuth2\AuthUser;

class ResourceAuthMiddleware extends AccessTokenMiddleware
{
    public function setExtraDataToRequest($request)
    {
        $auth_user = AuthUser::create();
        $request->merge(['auth_user' => $auth_user]);
    }
}
