<?php


namespace Sheba\Payment\Exceptions;


use Throwable;

class FailedToInitiate extends \Exception
{
    public function __construct($message = "Failed To Initiate Payment", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}