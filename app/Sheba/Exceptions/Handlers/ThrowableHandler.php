<?php namespace Sheba\Exceptions\Handlers;

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ThrowableHandler extends Handler
{
    protected function getCode()
    {
        return 500;
    }

    protected function getMessage()
    {
        if ($this->exception instanceof MethodNotAllowedHttpException) return 'Method is not allowed';
        if ($this->exception instanceof NotFoundHttpException) return 'Requested path not found';
        if ($this->exception instanceof RouteNotFoundException) return 'Route Not Found';
        return "Internal Server Error";
    }
}
