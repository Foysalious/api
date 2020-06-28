<?php namespace Sheba\Exceptions\Handlers;


class RouteNotFoundExceptionHandler extends Handler
{
    protected function getCode()
    {
        return 404;
    }

    protected function getMessage()
    {
        return 'Route Not Found';
    }
}
