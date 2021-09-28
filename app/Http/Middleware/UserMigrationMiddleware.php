<?php

namespace App\Http\Middleware;

use Sheba\OAuth2\AuthUser;

class UserMigrationMiddleware extends AccessTokenMiddleware
{
    public function setExtraDataToRequest($request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $user = $auth_user->getPartner();
        if (!$user) return;
        $type = strtolower(class_basename($user));
        $request->merge([$type => $user, 'type' => $type, 'user' => $user]);
    }
}
