<?php namespace Sheba\NID\Validations;


use Throwable;

class DigitException extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = 'Date of birth is required for 13 digit nid number';
        parent::__construct($message, $code, $previous);
    }
}
