<?php

use Sheba\Logs\ErrorLog;

if (!function_exists('logError')) {
    /**
     * @param $exception
     * @param null $request
     * @param null $message
     * @return void
     */
    function logError($exception, $request = null, $message = null)
    {
        (new ErrorLog())->setException($exception)->setRequest($request)->setErrorMessage($message)->send();
    }
}
