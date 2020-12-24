<?php

namespace Sheba\Referral\Exceptions;

use Throwable;

class AlreadyExistProfile extends \Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        if (empty($message))
            $message = "This mobile number already exists for a partner";
        $code=400;
        parent::__construct($message, $code, $previous);
    }

}
