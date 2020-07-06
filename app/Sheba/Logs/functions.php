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
        $log = (new ErrorLog())->setException($exception);
        if ($request) $log->setRequest($request);
        if ($message) $log->setErrorMessage($message);
        $log->send();
    }
}
