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
            return api_response($this->request, null, 401);
        }
        $user = $payload['avatar'];
        $model = "App\\Models\\" . ucfirst(camel_case($user['type']));

        $user = $model::where([
            ['id', $user['type_id']]
        ])->with('profile')->first();
        return $user;

    }

    public function setAuthenticateRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}