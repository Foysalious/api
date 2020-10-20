<?php namespace Sheba\Auth;

use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Authentication\Exceptions\AuthenticationFailedException;
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
            throw new AuthenticationFailedException();
        }
        $auth_user = new AuthUser();;
        $auth_user->setPayload($payload);
        if (!$auth_user->getAuthUser()) throw new AuthenticationFailedException();
        return $auth_user;

    }

    public function setAuthenticateRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}
