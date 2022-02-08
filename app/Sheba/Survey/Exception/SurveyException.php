<?php

namespace App\Sheba\Survey\Exception;

use Throwable;

class SurveyException extends \Exception
{
    public function __construct($message = "", $code = 409, Throwable $previous = null)
    {
        if ($message == "") $message = "Survey already exist";
        parent::__construct($message, $code, $previous);
    }
}