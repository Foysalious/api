<?php namespace App\Sheba\Sentry;

use Exception;

class SendSentryError
{
    private $exception;

    public function __construct(Exception $exception)
    {
        $this->exception = $exception;
        $this->send();
    }

    public function send()
    {
        app('sentry')->captureException($this->exception);
    }

}