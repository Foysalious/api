<?php namespace App\Http\Middleware;

use App\Models\Member;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class BusinessAuthMiddleware
{

    protected function handleAuth($role)
    {
        $payload = [];

        try {
            $token = JWTAuth::getToken();
            $payload = JWTAuth::getPayload($token)->toArray();
            dd($payload);
        } catch (JWTException $e) {
            $this->die(401, $e->getMessage());
        }

        if (!isset($payload['logistic_user'])) $this->die(401, 'User has no access');
        $this->user = Member::find($payload['logistic_user']['id']);
        if (!$this->user) $this->die(404, 'User not found.');
        $this->user->name = $payload['name'];

        $this->request->merge(['user' => $this->user]);
    }

    protected function getModifier()
    {
        return $this->user;
    }
}