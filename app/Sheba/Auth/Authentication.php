<?php namespace Sheba\Auth;

use Illuminate\Http\Request;
use Sheba\Authentication\AuthenticationFailedException;
use Sheba\Authentication\AuthUser;

interface Authentication
{
    /**
     * @throws AuthenticationFailedException
     */
    public function authenticate(): AuthUser;

    public function setAuthenticateRequest(Request $request);
}