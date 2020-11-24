<?php namespace Sheba\Ocr\Exceptions;

use Exception;
use Throwable;

class OcrServerError extends Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'NID OCR server not working as expected.';
        }
        parent::__construct($message, $code, $previous);
    }
}
