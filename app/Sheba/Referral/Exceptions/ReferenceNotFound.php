<?php

namespace Sheba\Referral\Exceptions;

use Throwable;

class ReferenceNotFound extends \Exception
{
    public function __construct($message = "", $code = 404, Throwable $previous = null)
    {
        if (empty($message))
            $message = "Reference not found for this user";
        parent::__construct($message, $code, $previous);
    }

}
