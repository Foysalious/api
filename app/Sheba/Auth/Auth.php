<?php namespace Sheba\Auth;

use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;

class Auth
{
    /** @var Authentication $authentication */
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

    /**
     * @return AuthUser
     * @throws AuthenticationFailedException
     */
    public function authenticate()
    {
        /** @var Authentication $authentication */
        $authentication = $this->authentication->setAuthenticateRequest($this->request);
        return $authentication->authenticate();
    }
}
