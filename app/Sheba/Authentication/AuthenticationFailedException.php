<?php namespace Sheba\Authentication;

use Exception;

class AuthenticationFailedException extends Exception
{
    public function report()
    {
        \Log::debug('User not found');
    }
}