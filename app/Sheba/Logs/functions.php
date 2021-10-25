<?php

use Sheba\Logs\ErrorLog;

if (!function_exists('logError')) {
    /**
     * @param Throwable$exception
     * @param null $request
     * @param null $message
     * @param array $extra
     * @return void
     */
    function logError(Throwable $exception, $request = null, $message = null, $extra = [])
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
     * @param Throwable $exception
     * @param array $extra
     * @return void
     */
    function logErrorWithExtra(Throwable $exception, array $extra)
    {
        logError($exception, null, null, $extra);
    }
}
