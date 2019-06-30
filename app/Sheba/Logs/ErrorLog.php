<?php namespace Sheba\Logs;

use Exception;

class ErrorLog
{
    private $exception;

    public function setException(Exception $exception)
    {
        $this->exception = $exception;
        return $this;
    }

    public function send()
    {
        app('sentry')->captureException($this->exception);
    }

}