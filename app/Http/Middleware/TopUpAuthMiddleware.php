<?php namespace App\Http\Middleware;

use Sheba\OAuth2\AuthUser;

class TopUpAuthMiddleware extends AccessTokenMiddleware
{
    public function setExtraDataToRequest($request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $user = $auth_user->getAvatar();
        if (!$user) return;
        $type = strtolower(class_basename($user));
        $request->merge([$type => $user, 'type' => $type, 'user' => $user]);
    }
}
