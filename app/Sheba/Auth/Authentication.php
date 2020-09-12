<?php namespace Sheba\Auth;

use Illuminate\Http\Request;
use Sheba\Authentication\AuthUser;
use Sheba\Authentication\Exceptions\AuthenticationFailedException;

interface Authentication
{
    /**
     * @throws AuthenticationFailedException
     */
    public function authenticate(): AuthUser;

    public function setAuthenticateRequest(Request $request);
}