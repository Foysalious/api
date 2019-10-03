<?php

namespace App\Exceptions;


use Exception;

class HyperLocationNotFoundException extends Exception
{
    public function report()
    {
        \Log::debug('User not found');
    }
}