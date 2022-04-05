<?php

namespace Sheba\Helpers\Logger;


use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Sheba\UserAgentInformation;

class ApiLogger
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Response
     */
    private $response;

    public function __construct(Request $request, Response $response)
    {
        $this->request  = $request;
        $this->response = $response;
    }

    public function log()
    {
        $logPath = storage_path() . '/logs/api.log';
        try {
            $api_url = $this->request->getUri();
            if ($api_url == "http://127.0.0.1/") return;
            $agent = new UserAgentInformation();
            $agent->setRequest($this->request);
            $ip            = $agent->getIp();
            $payload       = json_encode($this->request->all());
            $headers       = json_encode($this->request->header());
            $userAgent     = $agent->getUserAgent();
            $response_     = $this->response->getContent();
            $response_data = json_decode($response_, true);
            $status_code   = $response_data && array_key_exists('code', $response_data) ? $response_data['code'] : $this->response->getStatusCode();
            $len           = mb_strlen($response_, '8bit');
            if ($len > 10000) {
                $response_ = mb_strcut($response_, 0, 10000);
            }
            $logger = new Logger("api_logger");
            $logger->pushHandler((new StreamHandler("$logPath"))->setFormatter(new JsonFormatter()), Logger::INFO);
            $logger->info("requestINFO", ['uri' => $api_url, "headers" => $headers, "status_code" => $status_code, "payload" => $payload, "agent" => $userAgent, "response" => $response_, "ip" => $ip, "app_version" => $this->request->header('version-code'), "portal" => $agent->getPortalName()]);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }

}