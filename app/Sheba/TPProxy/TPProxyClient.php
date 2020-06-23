<?php namespace Sheba\TPProxy;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;

class TPProxyClient
{
    /** @var HttpClient */
    private $httpClient;
    private $proxyUrl;

    public function __construct(HttpClient $client)
    {
        $this->httpClient = $client;
        $this->proxyUrl = config('sheba.tp_proxy_url');
    }

    /**
     * @param TPRequest $request
     * @return mixed
     * @throws TPProxyServerError
     */
    public function call(TPRequest $request)
    {
        try {
            $response = $this->httpClient->post($this->proxyUrl, [
                'form_params' => [
                    'url' => $request->getUrl(),
                    'method' => $request->getMethod(),
                    'input' => $request->getInput(),
                    'headers' => $request->getHeaders()
                ],
                'timeout' => 120,
                'read_timeout' => 300,
                'connect_timeout' => 120
            ]);

            $proxy_response = $response->getBody()->getContents();
            if (!$proxy_response) throw new TPProxyServerError();
            $proxy_response = json_decode($proxy_response);
            if ($proxy_response->code != 200) throw new TPProxyServerError($proxy_response->message);
            return $proxy_response->tp_response;
        } catch (GuzzleException $e) {
            throw new TPProxyServerError($e->getMessage());
        }
    }
}
