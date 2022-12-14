<?php namespace App\Http\Middleware;

use Sheba\OAuth2\AuthUser;

class AccountingAuthMiddleware extends AccessTokenMiddleware
{
    public function setExtraDataToRequest($request)
    {
        /** @var AuthUser $auth_user */
        $auth_user = $request->auth_user;
        $user      = $auth_user->getPartner();
        if (!$user) return;
        $type = strtolower(class_basename($user));
        $request->merge([$type => $user, 'type' => $type, 'user' => $user]);
        $manager_resource = $auth_user->getResource();
        if ($manager_resource) {
            $request->merge(['manager_resource' => $manager_resource]);
        }
    }
}