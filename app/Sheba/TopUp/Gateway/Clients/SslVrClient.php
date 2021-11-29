<?php namespace Sheba\TopUp\Gateway\Clients;

use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\TopUp\Exception\GatewayTimeout;

class SslVrClient
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
     * @param $data
     * @return object
     * @throws \Exception
     * @throws GatewayTimeout
     */
    public function call($data)
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
        } catch (ConnectException $e) {
            if (isTimeoutException($e)) throw new GatewayTimeout($e->getMessage());

            throw $e;
        } catch (GuzzleException $e) {
            throw new Exception("VR proxy server error: ". $e->getMessage());
        }
    }
}
