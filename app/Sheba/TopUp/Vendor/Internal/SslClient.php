<?php namespace Sheba\TopUp\Vendor\Internal;

use App\Models\TopUpOrder;
use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\TopUp\Vendor\Response\SslResponse;
use Sheba\TopUp\Vendor\Response\TopUpResponse;

class SslClient
{
    const VR_PROXY_RECHARGE_ACTION = "recharge";
    const VR_PROXY_BALANCE_ACTION = "get_balance";
    const VR_PROXY_STATUS_ACTION = "get_status";

    /** @var HttpClient */
    private $httpClient;

    private $proxyUrl;
    private $clientId;
    private $clientPassword;
    private $topUpUrl;

    public function __construct(HttpClient $client)
    {
        $this->httpClient = $client;
        $this->proxyUrl = config('topup.ssl.proxy_url');
        $this->topUpUrl = config('topup.ssl.url');
        $this->clientId = config('topup.ssl.client_id');
        $this->clientPassword = config('topup.ssl.client_password');
    }

    /**
     * @param TopUpOrder $topup_order
     * @return TopUpResponse
     * @throws \Exception
     */
    public function recharge(TopUpOrder $topup_order): TopUpResponse
    {
        $ssl_response = new SslResponse();
        $ssl_response->setResponse($this->call([
            "action" => self::VR_PROXY_RECHARGE_ACTION,
            'guid' => randomString(20, 1, 1),
            'payee_mobile' => $topup_order->payee_mobile,
            'operator_id' => $this->getOperatorId($topup_order->payee_mobile),
            'connection_type' => $topup_order->payee_mobile_type,
            'sender_id' => "redwan@sslwireless.com",
            'priority' => 1,
            'success_url' => config('sheba.api_url') . '/v2/top-up/success/ssl',
            'fail_url' => config('sheba.api_url') . '/v2/top-up/fail/ssl',
            'calling_method' => "GET",
            'amount' => $topup_order->amount
        ]));
        return $ssl_response;
    }

    /**
     * @return mixed
     * @throws Exception
     */
    public function getBalance()
    {
        return $this->call(["action" => self::VR_PROXY_BALANCE_ACTION]);
    }

    /**
     * @param $guid
     * @param $vr_guid
     * @return mixed
     * @throws Exception
     */
    public function getRecharge($guid, $vr_guid)
    {
        return $this->call([
            'action' => self::VR_PROXY_STATUS_ACTION,
            'guid' => $guid,
            'vr_guid' => $vr_guid
        ]);
    }

    private function getOperatorId($mobile_number)
    {
        $mobile_number = formatMobile($mobile_number);
        if (preg_match("/^(\+88017)/", $mobile_number) || preg_match("/^(\+88013)/", $mobile_number)) {
            return 1;
        } elseif (preg_match("/^(\+88019)/", $mobile_number) || preg_match("/^(\+88014)/", $mobile_number)) {
            return 2;
        } elseif (preg_match("/^(\+88018)/", $mobile_number)) {
            return 3;
        } elseif (preg_match("/^(\+88016)/", $mobile_number)) {
            return 6;
        } elseif (preg_match("/^(\+88015)/", $mobile_number)) {
            return 5;
        } else {
            throw new \InvalidArgumentException('Invalid Mobile for ssl topup.');
        }
    }

    /**
     * @param $data
     * @return object
     * @throws \Exception
     */
    private function call($data)
    {
        $common = [
            'url' => $this->topUpUrl,
            'client_id' => $this->clientId,
            'client_password' => $this->clientPassword
        ];

        try {
            $response = $this->httpClient->request('POST', $this->proxyUrl, [
                'form_params' => $common + $data,
                'timeout' => 60,
                'read_timeout' => 60,
                'connect_timeout' => 60
            ]);

            $proxy_response = $response->getBody()->getContents();
            if (!$proxy_response) throw new Exception("VR proxy server not working.");
            $proxy_response = json_decode($proxy_response);
            if ($proxy_response->code != 200) throw new Exception("VR proxy server error: ". $proxy_response->message);
            return $proxy_response->vr_response;
        } catch (GuzzleException $e) {
            throw new Exception("VR proxy server error: ". $e->getMessage());
        }
    }
}
