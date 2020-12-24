<?php namespace Sheba\TopUp\Exception;

use App\Exceptions\ApiValidationException;
use Throwable;

class PinMismatchException extends ApiValidationException
{
    public function __construct($message = "Pin Mismatch", $code = 403, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
