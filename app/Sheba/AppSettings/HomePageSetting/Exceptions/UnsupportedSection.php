<?php namespace Sheba\AppSettings\HomePageSetting\Exceptions;

use Exception;
use Throwable;

class UnsupportedSection extends Exception
{
    public function __construct($section, $code = 402, Throwable $previous = null)
    {
        $message = "$section is not supported.";
        parent::__construct($message, $code, $previous);

    }
}