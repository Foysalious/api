<?php

namespace App\Http\Middleware\RequestResponseLog;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class MarketplaceRequestResponseLogMiddleware extends RequestResponseLog
{
    protected function getRequest($request)
    {
        return json_encode($request->except('access_token', 'remember_token', 'token'));
    }


    protected function logEntry()
    {
        $handler = new StreamHandler(storage_path('logs/marketplace-analytics-' . date('Y-m-d') . '.log'), Logger::INFO);
        $handler->setFormatter(new LineFormatter(null, null, true, true));
        $monolog = new Logger('logger');
        $monolog->pushHandler($handler);
        $monolog->info(" method:$this->method host:$this->host full_url:$this->full_url response_time:$this->response_time req:$this->req res:$this->res");
    }
}
