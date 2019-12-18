<?php


namespace Sheba\Loan\Exceptions;


use Throwable;

class InvalidStatusTransaction extends \Exception
{
        public function __construct($message = "", $code = 0, Throwable $previous = null)
        {
            if (empty($message)) $message="You can not transact to this status from here";
            parent::__construct($message, $code, $previous);
        }
}