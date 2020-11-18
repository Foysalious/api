<?php namespace App\Http\Middleware;

use App\Models\Profile;
use Closure;
use Sheba\OAuth2\AuthUser;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWTAuthentication extends AccessTokenMiddleware
{
    protected function setExtraDataToRequest($request)
    {
        if (!$this->accessToken->accessTokenRequest->profile) return;
        $auth_user = AuthUser::create();
        $request->merge(['profile' => $this->accessToken->accessTokenRequest->profile, 'auth_info' => $auth_user->getAttributes()]);
    }
}
