<?php

namespace Sheba\Referral\Exceptions;
use Throwable;

class AlreadyReferred extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null) {
        if (empty($message)) $message="This mobile number is already referred";
        $code=404;
        parent::__construct($message, $code, $previous);
    }

}
