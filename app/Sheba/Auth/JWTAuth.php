<?php namespace Sheba\Auth;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth as Auth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JWTAuth implements Authentication
{
    private $request;

    public function authenticate()
    {
        try {
            $payload = [];
            $token = Auth::getToken();
            $payload = Auth::getPayload($token)->toArray();
        } catch (JWTException $e) {
            return null;
        }
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