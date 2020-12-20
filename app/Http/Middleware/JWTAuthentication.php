<?php namespace App\Http\Middleware;


class JWTAuthentication extends AccessTokenMiddleware
{
    protected function setExtraDataToRequest($request)
    {
        if (!$this->authorizationToken->authorizationRequest->profile) return;
        $auth_user = $request->auth_user;
        $request->merge(['profile' => $this->authorizationToken->authorizationRequest->profile, 'auth_info' => $auth_user->getAttributes()]);
    }
}
