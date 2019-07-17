<?php namespace Sheba\Auth;

use Illuminate\Http\Request;

interface Authentication
{
    public function authenticate();

    public function setAuthenticateRequest(Request $request);
}