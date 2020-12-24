<?php namespace Sheba\Exceptions\Handlers;

class MethodNotAllowedHttpExceptionHandler extends Handler
{
    protected function getCode()
    {
        return 405;
    }

    protected function getMessage()
    {
        return 'Method is not allowed';
    }
}
