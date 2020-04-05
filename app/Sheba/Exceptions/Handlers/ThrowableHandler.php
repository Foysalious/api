<?php namespace Sheba\Exceptions\Handlers;

use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class ThrowableHandler extends Handler
{
    protected function getCode()
    {
        return isset(constants('API_RESPONSE_CODES')[$this->exception->getCode()]) ? $this->exception->getCode() : 500;
    }

    protected function getMessage()
    {
        if ($this->exception instanceof MethodNotAllowedHttpException) return 'Method is not allowed';
        if ($this->exception instanceof NotFoundHttpException) return 'Requested path not found';
        if ($this->exception instanceof RouteNotFoundException) return 'Route Not Found';
        return empty(trim($this->exception->getMessage())) ? "Internal Server Error" : $this->exception->getMessage();
    }
}
