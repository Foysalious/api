<?php namespace App\Http\Middleware;


class AccountingAuthMiddleware extends AccessTokenMiddleware
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