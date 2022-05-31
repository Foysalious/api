<?php

namespace App\Sheba\DynamicForm\Exceptions;

use Throwable;
use App\Exceptions\HttpException;

class FormValidationException extends HttpException
{
    public function __construct($message = "", $code = 500, Throwable $previous = null)
    {
        if ($message == "") $message = "Form validation failed";
        parent::__construct($message, $code, $previous);
    }
}