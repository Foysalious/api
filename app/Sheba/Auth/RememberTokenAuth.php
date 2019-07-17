<?php namespace Sheba\Auth;

use Illuminate\Http\Request;

class RememberTokenAuth implements Authentication
{
    private $request;

    public function authenticate()
    {
        $model = "App\\Models\\" . ucfirst(camel_case($this->request->type));
        $user = $model::where([
            ['id', $this->request->type_id],
            ['remember_token', $this->request->token]
        ])->with('profile')->first();
        return $user;
    }

    public function setAuthenticateRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }
}