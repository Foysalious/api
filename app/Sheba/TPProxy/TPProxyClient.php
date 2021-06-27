<?php namespace Sheba\TPProxy;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;

class TPProxyClient
{
    protected $proxyUrl;
    /** @var HttpClient */
    private $httpClient;

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
                'timeout' => $request->getTimeout(),
                'read_timeout' => $request->getReadTimeout(),
                'connect_timeout' => $request->getConnectTimeout()
            ]);
            $proxy_response = $response->getBody()->getContents();
            if (!$proxy_response) throw new TPProxyServerError();
            $proxy_response = json_decode($proxy_response);
            if ($proxy_response->code != 200) throw new TPProxyServerError($proxy_response->message);
            return $proxy_response->tp_response;
        } catch (ConnectException $e) {
            if (isTimeoutException($e)) {
                logErrorWithExtra($e, ['request' => $request->toArray()]);
                throw new TPProxyServerTimeout($e->getMessage());
            }

            throw $e;
        } catch (GuzzleException $e) {
            logErrorWithExtra($e, ['request' => $request->toArray()]);
            throw new TPProxyServerError($e->getMessage());
        }
    }

    /**
     * @param $url
     * @param $method
     * @param $options
     * @return mixed
     * @throws TPProxyServerError
     */
    public function callWithFile($url, $method, $options)
    {
        $options = array_merge($options, [
            'timeout' => 120,
            'read_timeout' => 300,
            'connect_timeout' => 120
        ]);
        $options['multipart'][] = ['name' => 'url', 'contents' => $url];
        $res = $this->httpClient->post($this->proxyUrl . '/file_request.php', $options);
        $proxy_response = $res->getBody()->getContents();
        if (!$proxy_response) throw new TPProxyServerError();
        $proxy_response = json_decode($proxy_response);
        if ($proxy_response->code != 200) throw new TPProxyServerError($proxy_response->message);
        return $proxy_response->tp_response;
    }
}
