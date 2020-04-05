<?php namespace Sheba\Auth;

use Illuminate\Http\Request;
use Sheba\Authentication\AuthenticationFailedException;
use Sheba\Authentication\AuthUser;
use Tymon\JWTAuth\Facades\JWTAuth as Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JWTAuth implements Authentication
{
    private $request;

    public function authenticate(): AuthUser
    {
        try {
            $payload = [];
            $token = Auth::getToken();
            $payload = Auth::getPayload($token)->toArray();
        } catch (JWTException $e) {
            throw new AuthenticationFailedException("User not found");
        }
        $auth_user = new AuthUser();
        $auth_user->setPayload($payload);
        if (!$auth_user->getProfile()) throw new AuthenticationFailedException("User not found");
        return $auth_user;
        $user = $payload['avatar'];
        $model = "App\\Models\\" . ucfirst(camel_case($user['type']));
        return $model::find($user['type_id']);

    }

    public function setAuthenticateRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}
