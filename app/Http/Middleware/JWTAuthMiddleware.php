<?php namespace App\Http\Middleware;


use Sheba\OAuth2\AuthUser;

class JWTAuthMiddleware extends AccessTokenMiddleware
{
    protected function setExtraDataToRequest($request)
    {
        $auth_user = AuthUser::create();
        $user = $auth_user->getAvatar();
        if (!$user) return;
        $type = strtolower(class_basename($user));
        $request->merge([$type => $user, 'type' => $type, 'user' => $user]);
    }
}
