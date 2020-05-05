<?php namespace Sheba\Authentication;

use App\Exceptions\ApiValidationException;

class AuthenticationFailedException extends ApiValidationException
{
    public function report()
    {
        \Log::debug('User not found');
    }
}