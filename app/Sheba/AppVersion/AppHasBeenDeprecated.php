<?php namespace Sheba\AppVersion;

use Exception;
use Throwable;

class AppHasBeenDeprecated extends Exception
{
    public function __construct(App $app, $message = "", $code = 410, Throwable $previous = null)
    {
        $app_name = $app->getMarketName();
        $version = $app->getVersionName();
        $from = $app->isIos() ? "app store" : "play store";

        if (!$message || $message == '') {
            $message = "$app_name v$version has been deprecated. Please update your app to latest version from $from.";
        }

        parent::__construct($message, $code, $previous);
    }
}
