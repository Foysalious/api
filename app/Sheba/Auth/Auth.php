<?php namespace Sheba\Auth;

use Illuminate\Http\Request;

class Auth
{
    private $authentication;
    private $request;

    public function setStrategy(Authentication $authentication)
    {
        $this->authentication = $authentication;
        return $this;
    }

    public function setRequest(Request $request)
    {
        $this->request = $request;
        return $this;
    }

    public function authenticate()
    {
        return $this->authentication->setAuthenticateRequest($this->request)->authenticate();
    }
}