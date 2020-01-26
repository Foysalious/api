<?php

namespace Sheba\PartnerOrder\Exceptions;

use Throwable;

class PartnerAddressNotFound extends \Exception
{
    public function __construct($message = "Partner address can not be found", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
