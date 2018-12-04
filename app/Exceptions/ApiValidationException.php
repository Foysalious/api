<?php
/**
 * Created by PhpStorm.
 * User: Tech Land
 * Date: 12/3/2018
 * Time: 4:36 PM
 */

namespace App\Exceptions;


use Exception;
use Throwable;

class ApiValidationException extends Exception
{
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);

    }

    protected function render(Exception $e)
    {
        parent::render($e);
    }
}