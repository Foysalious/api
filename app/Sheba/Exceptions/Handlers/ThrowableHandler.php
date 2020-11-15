<?php namespace Sheba\Exceptions\Handlers;

class ThrowableHandler extends Handler
{
    protected function getCode()
    {
        return 500;
    }

    protected function getMessage()
    {
        return 'Something went wrong.';
    }
}
