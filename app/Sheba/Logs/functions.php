<?php

use Sheba\Logs\ErrorLog;

if (!function_exists('logError')) {
    /**
     * @param $exception
     * @param null $request
     * @param null $message
     * @param array $extra
     * @return void
     */
    function logError($exception, $request = null, $message = null, $extra = [])
    {
        $log = (new ErrorLog())->setException($exception);
        if ($request) $log->setRequest($request);
        if ($message) $log->setErrorMessage($message);
        if (!empty($extra)) $log->setExtra($extra);
        $log->send();
    }
}


if (!function_exists('logErrorWithExtra')) {
    /**
     * @param $exception
     * @param array $extra
     * @return void
     */
    function logErrorWithExtra($exception, array $extra)
    {
        logError($exception, null, null, $extra);
    }
}
