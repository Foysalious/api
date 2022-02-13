<?php namespace Sheba\Mail;

use Illuminate\Mail\Mailer;
use Illuminate\Support\Facades\Facade;

/**
 * @see Mailer
 */
class BusinessMail extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'business_mailer';
    }
}
