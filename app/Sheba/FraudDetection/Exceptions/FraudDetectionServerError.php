<?php namespace Sheba\FraudDetection\Exceptions;

use Exception;
use Throwable;

class FraudDetectionServerError extends Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Fraud detection server not working as expected.';
        }
        parent::__construct($message, $code, $previous);
    }
}
