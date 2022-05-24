<?php


namespace Sheba\AccountingEntry\Exceptions;


use Sheba\Exceptions\Exceptions\ExceptionForClient;
use Throwable;

class ContactDoesNotExistInDueTracker extends ExceptionForClient
{
    public function __construct($message = "Contact does not exist in due tracker", $code = 400, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
