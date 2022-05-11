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

            $headers = $this->request->header();
            $headers = array_only($headers, ['x-real-ip', 'x-forwarded-for', 'custom-headers', 'platform-name', 'user-id', 'lat', 'lng']);

            $payload = json_encode($this->request->except(['password', 'secret', 'token']));
            if ($this->request->has('lat') && $this->request->has('long')) {
                $headers['lat'] = $this->request->lat;
                $headers['lng'] = $this->request->long;
            }
            $response_     = $this->response->getContent();
            $response_data = json_decode($response_, true);

            $status_code = $response_data && array_key_exists('code', $response_data) ? $response_data['code'] : $this->response->getStatusCode();

            if (mb_strlen($response_, '8bit') > 10000) {
                $response_ = mb_strcut($response_, 0, 10000);
            }
            $profile_id = $this->getUser($agent->getPortalName());

            $logger = new Logger("api_logger");
            $logger->pushHandler((new RotatingFileHandler("$logPath", 2))->setFormatter(new JsonFormatter()), Logger::INFO);
            $logger->info("requestINFO", [
                'uri'         => $api_url,
                "headers"     => json_encode($headers),
                "status_code" => $status_code,
                "payload"     => $payload,
                "agent"       => $agent->getUserAgent(),
                "response"    => $response_,
                "ip"          => $agent->getIp(),
                "app_version" => $app ? (int)$app->getVersionCode() : 0,
                "portal"      => $agent->getPortalName(),
                "user_info"   => $profile_id,
                "method"      => $this->request->getMethod(),
                "project"     => 'api'
            ]);
        } catch (\Throwable $e) {
            \Log::error($e->getMessage());
        }
    }

    private function getUser($portal)
    {

        try {
            $data = array_only((array)AuthUser::create()->toArray(), ['profile', 'resource', 'partner', 'member', 'business_member', 'member', 'affiliate', 'avatar', 'customers']);
            if (!array_key_exists('avatar', $data) || empty($data['avatar'])) {
                if ($portal == 'bondhu-app' && (array_key_exists('affiliate', $data) && !empty($data['affiliate']))) {
                    $data['avatar'] = ['type' => 'affiliate', 'type_id' => $data['affiliate']['id']];
                } elseif (in_array($portal, ['employee-app', 'business-portal']) && (array_key_exists('business_member', $data) && !empty($data['business_member']))) {
                    $data['avatar'] = ['type' => 'employee', 'type_id' => $data['business_member']['id']];
                } elseif ($portal == 'customer-app' && (array_key_exists('customer', $data) && !empty($data['customer']))) {
                    $data['avatar'] = ['type' => 'customer', 'type_id' => $data['customer']['id']];
                } elseif ($portal == 'manager-app' && (array_key_exists('resource', $data) && !empty($data['resource']))) {
                    $data['avatar'] = ['type' => 'partner', 'type_id' => $data['resource']['partner']['id']];
                } elseif ($portal == 'resource-app' && array_key_exists('resource', $data) && !empty($data['resource'])) {
                    $data['avatar'] = ['type' => 'resource', 'type_id' => $data['resource']['id']];
                } else {
                    $data['avatar'] = ['type' => 'public', 'type_id' => 0];
                }
            }
            return $data;
        } catch (\Throwable $e) {
            preg_match('/(partners|resources|vendor|affiliates|member|customers|businesses)\/([0-9]+.)\//',
                $this->request->getUri(), $match);
            $map = ['partners' => 'partner', 'affiliates' => 'affiliate', 'resources' => 'resource', 'members' => 'member', 'customers' => 'customer', 'vendor' => 'vendor', 'businesses' => 'business'];
            if (count($match) > 2) {
                return [
                    'avatar' => [
                        'type'    => $map[$match[1]],
                        'type_id' => $match[2]
                    ]
                ];
            } else {
                return [
                    'avatar' => [
                        'type'    => 'public',
                        'type_id' => 0
                    ]
                ];
            }
        }
    }
}