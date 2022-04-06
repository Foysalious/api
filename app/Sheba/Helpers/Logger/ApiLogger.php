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

            $payload = json_encode($this->request->except(['password']));

            $headers = $this->request->header();
            $headers = array_except($headers, ['authorization']);
            $headers = json_encode($headers);

            $response_     = $this->response->getContent();
            $response_data = json_decode($response_, true);

            $status_code = $response_data && array_key_exists('code', $response_data) ? $response_data['code'] : $this->response->getStatusCode();

            if (mb_strlen($response_, '8bit') > 10000) {
                $response_ = mb_strcut($response_, 0, 10000);
            }
            $user      = $this->getUser();
            $user_type = null;
            $user_id   = null;
            if ($user) {
                $user_id   = is_string($user) ? $user : $user->id;
                $user_type = is_string($user) ? null : class_basename($user);
            }
            $logger = new Logger("api_logger");
            $logger->pushHandler((new StreamHandler("$logPath"))->setFormatter(new JsonFormatter()), Logger::INFO);
            $logger->info("requestINFO", [
                'uri'         => $api_url,
                "headers"     => $headers,
                "status_code" => $status_code,
                "payload"     => $payload,
                "agent"       => $agent->getUserAgent(),
                "response"    => $response_,
                "ip"          => $agent->getIp(),
                "app_version" => $agent->getApp()->getVersionCode(),
                "portal"      => $agent->getPortalName(),
                "user_type"   => $user_type,
                "user_id"     => $user_id
            ]);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }

    private function getUser()
    {
        if ($this->request->affiliate) return $this->request->affiliate;
        elseif ($this->request->customer) return $this->request->customer;
        elseif ($this->request->partner) return $this->request->partner;
        elseif ($this->request->vendor) return $this->request->vendor;
        elseif ($this->request->business) return $this->request->business;
        else return null;
    }
}