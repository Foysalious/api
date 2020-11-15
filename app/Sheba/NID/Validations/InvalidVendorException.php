<?php namespace Sheba\NID\Validations;

use Sheba\Exceptions;
use Throwable;

class InvalidVendorException extends \Exception
{
    /**
     * InvalidVendorException constructor.
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = "Vendor is invalid,Please set a valid vendor";
        parent::__construct($message, $code, $previous);
    }
}
