<?php
/**
 * Created by PhpStorm.
 * User: tonmoy
 * Date: 1/10/19
 * Time: 6:40 PM
 */

namespace App\Exceptions;


class InvalidModeratorException extends \Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == '') {
            $message = 'Invalid Moderator';
        }
        parent::__construct($message, $code, $previous);

    }

    protected function render(Exception $e)
    {
        parent::render($e);
    }
}