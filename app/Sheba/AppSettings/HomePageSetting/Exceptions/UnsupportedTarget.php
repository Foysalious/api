<?php namespace Sheba\AppSettings\HomePageSetting\Exceptions;

use Exception;
use Throwable;

class UnsupportedTarget extends Exception
{
    public function __construct($target, $code = 402, Throwable $previous = null)
    {
        $message = "$target is not supported.";
        parent::__construct($message, $code, $previous);

    }
}