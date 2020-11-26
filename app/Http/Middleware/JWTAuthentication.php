<?php namespace App\Http\Middleware;


class JWTAuthentication extends AccessTokenMiddleware
{
    protected function setExtraDataToRequest($request)
    {
        if (!$this->accessToken->accessTokenRequest->profile) return;
        $auth_user = $request->auth_user;
        $request->merge(['profile' => $this->accessToken->accessTokenRequest->profile, 'auth_info' => $auth_user->getAttributes()]);
    }
}
