<?php namespace App\Sheba\InventoryService\Exceptions;

use Exception;
use Throwable;


class InventoryServiceServerError extends Exception
{
    public function __construct($message = "", $code = 402, Throwable $previous = null)
    {
        if (!$message || $message == "") {
            $message = 'Inventory Service server not working as expected.';
        }
        parent::__construct($message, $code, $previous);

    }

}