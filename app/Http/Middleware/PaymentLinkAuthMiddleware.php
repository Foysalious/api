<?php namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sheba\Auth\Auth;
use Sheba\Auth\JWTAuth;
use Sheba\OAuth2\AuthUser;

class PaymentLinkAuthMiddleware extends AccessTokenMiddleware
{
    public function setExtraDataToRequest($request)
    {
        $auth_user = $request->auth_user;
        $user = $auth_user->getAvatar();
        if (!$user) return;
        $type = strtolower(class_basename($user));
        $request->merge([$type => $user, 'type' => $type, 'user' => $user]);
    }
}
