<?php

namespace Sheba\Payment\Methods\OkWallet\Exception;

use Throwable;

class KeyEncryptionFailed extends \Exception
{
    public function __construct($message = "Key Encryption Failed", $code = 0, Throwable $previous = null) { parent::__construct($message, $code, $previous); }

}
