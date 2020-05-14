<?php namespace Sheba\Payment\Methods\PortWallet;


use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

class Client
{
    /** @var HttpClient */
    private $httpClient;

    private $baseUrl;
    private $appKey;
    private $secretKey;

    CONST NAME = 'port_wallet';

    public function __construct(HttpClient $client)
    {
        $this->httpClient = $client;

        $this->baseUrl = config('port_wallet.base_url');
        $this->appKey = config('port_wallet.app_key');
        $this->secretKey = config('port_wallet.secret_key');
    }

    /**
     * @param $uri
     * @return array
     */
    public function get($uri)
    {
        return $this->call('get', $uri, $this->getOptionsForGet());
    }

    /**
     * @param $uri
     * @param $data
     * @return array
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $this->getOptionsForPost($data));
    }

    /**
     * @param $method
     * @param $uri
     * @param $options
     * @return array
     */
    private function call($method, $uri, $options)
    {
        $is_500 = false;

        try {
            $res = $this->httpClient->request(strtoupper($method), $this->makeUrl($uri), $options);
        } catch (GuzzleException $e) {
            $res = $e->getResponse();

            if($res->getStatusCode() >= 500) {
                $is_500 = true;
                $res = (object) [
                    "result" => "failed",
                    "error" => [
                        "message" => $e->getMessage(),
                        "reason" => $res->getReasonPhrase()
                    ]
                ];
            }
        }

        return $is_500 ? $res : decodeGuzzleResponse($res, false);
    }

    /**
     * @param $uri
     * @return string
     */
    private function makeUrl($uri)
    {
        return rtrim($this->baseUrl, '/') . "/" . ltrim($uri, '/');
    }

    private function getOptionsForGet()
    {
        return $this->getOptions();
    }

    private function getOptionsForPost($data)
    {
        return $this->getOptions($data);
    }

    private function getOptions($data = null)
    {
        $options['headers'] = $this->getHeaders();
        if ($data) {
            $options['json'] = $data;
        }
        return $options;
    }

    private function getHeaders()
    {
        $token = base64_encode($this->appKey . ":" . md5($this->secretKey . time()));
        return [
            'Authorization' => "Bearer $token",
            "Content-Type" => "application/json"
        ];
    }
}
