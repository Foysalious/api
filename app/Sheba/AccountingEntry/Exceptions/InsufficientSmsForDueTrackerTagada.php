<?php


namespace Sheba\AccountingEntry\Exceptions;


use Sheba\Exceptions\Exceptions\ExceptionForClient;
use Throwable;

class InsufficientSmsForDueTrackerTagada extends ExceptionForClient
{
    public function __construct($message = "তাগাদা পাঠানোর জন্য পর্যাপ্ত এসএমএস নেই", $code = 402, Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }

}
