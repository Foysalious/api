<?php

namespace Sheba\Helpers\Logger;


use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Sheba\OAuth2\AuthUser;
use Sheba\UserAgentInformation;
use Symfony\Component\HttpFoundation\Response;

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

    public function __construct($request, $response)
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
            $app = $agent->getApp();

            $payload = json_encode($this->request->except(['password', 'secret', 'token']));

            $headers = $this->request->header();
            $headers = array_only($headers, ['x-real-ip', 'x-forwarded-for', 'custom-headers', 'platform-name', 'user-id']);
            $headers = json_encode($headers);

            $response_     = $this->response->getContent();
            $response_data = json_decode($response_, true);

            $status_code = $response_data && array_key_exists('code', $response_data) ? $response_data['code'] : $this->response->getStatusCode();

            if (mb_strlen($response_, '8bit') > 10000) {
                $response_ = mb_strcut($response_, 0, 10000);
            }
            $profile_id = $this->getUser();

            $logger = new Logger("api_logger");
            $logger->pushHandler((new RotatingFileHandler("$logPath", 2))->setFormatter(new JsonFormatter()), Logger::INFO);
            $logger->info("requestINFO", [
                'uri'         => $api_url,
                "headers"     => $headers,
                "status_code" => $status_code,
                "payload"     => $payload,
                "agent"       => $agent->getUserAgent(),
                "response"    => $response_,
                "ip"          => $agent->getIp(),
                "app_version" => $app ? $app->getVersionCode() : $this->request->header('version-code'),
                "portal"      => $agent->getPortalName(),
                "user_info"   => $profile_id,
                "method"      => $this->request->getMethod()
            ]);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }

    private function getUser()
    {

        try {
            return array_only(AuthUser::create()->toArray(), ['profile', 'resource', 'partner', 'member', 'business_member', 'member', 'affiliate', 'avatar']);
        } catch (\Throwable $e) {
            preg_match('/(partners)\/([0-9]+.)\/|(affiliates)\/([0-9]+.)\/|(customers)\|([0-9]+.)\/|(member)\/([0-9]+.)\/|(resources)\/([0-9]+.)\//',
                $this->request->getUri(), $match);
            return $match;
        }
    }
}