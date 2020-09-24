<?php namespace Sheba\Exceptions\Handlers;


class NotFoundHttpExceptionHandler extends Handler
{
    protected function getCode()
    {
        return 404;
    }

    protected function getMessage()
    {
        return 'Requested path not found';
    }
}
