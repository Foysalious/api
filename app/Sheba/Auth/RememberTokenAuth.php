<?php namespace Sheba\Auth;

use Illuminate\Http\Request;
use Sheba\Authentication\AuthenticationFailedException;
use Sheba\Authentication\AuthUser;

class RememberTokenAuth implements Authentication
{
    private $request;

    public function authenticate(): AuthUser
    {
        $model = "App\\Models\\" . ucfirst(camel_case($this->request->type));
        $user = $model::where([
            ['id', $this->request->type_id],
            ['remember_token', $this->request->token]
        ])->with('profile')->first();
        if (!$user) throw new AuthenticationFailedException("User not found");
        $auth_user = new AuthUser();
        $auth_user->setProfile($user->profile);
        return $auth_user;
    }

    public function setAuthenticateRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}