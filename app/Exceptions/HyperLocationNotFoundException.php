<?php namespace App\Exceptions;


class HyperLocationNotFoundException extends ApiValidationException
{
    public function report()
    {
        \Log::debug('Location outside sheba service zone');
    }
}
