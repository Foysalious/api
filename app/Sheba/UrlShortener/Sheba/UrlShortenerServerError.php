<?php

namespace App\Sheba\UrlShortener\Sheba;

use Exception;
use Throwable;

class UrlShortenerServerError extends Exception
{
    public function __construct($message = "", $code = 406, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'UrlShortener server not working as expected.';
        }
        parent::__construct($message, $code, $previous);

    }
}