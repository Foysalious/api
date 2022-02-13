<?php namespace Sheba\TopUp\Exception;

use Exception;
use Throwable;

class TopUpStillNotResolvedException extends Exception
{
    private $response;

    public function __construct($response, $message = "Still not resolved", $code = 404, Throwable $previous = null)
    {
        $this->response = $response;
        parent::__construct($message, $code, $previous);
    }

    public function getResponse()
    {
        return $this->response;
    }
}