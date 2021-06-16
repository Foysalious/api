<?php namespace Sheba\TPProxy;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;

class NagadProxyClient
{
    protected $proxyUrl;
    /** @var HttpClient */
    private $httpClient;

    public function __construct(HttpClient $client)
    {
        $this->httpClient = $client;
        $this->proxyUrl = config('sheba.nagad_proxy_url');
    }

    /**
     * @param NagadRequest $request
     * @return mixed
     * @throws TPProxyServerError
     * @throws TPProxyServerTimeout
     */
    public function call(NagadRequest $request)
    {
        try {
            $response = $this->httpClient->post($this->proxyUrl, [
                'form_params' => [
                    'url' => $request->getUrl(),
                    'method' => $request->getMethod(),
                    'input' => $request->getInput(),
                    'headers' => $request->getHeaders(),
                    'store_data' => $request->getStoreData()
                ],
                'timeout' => 60,
                'read_timeout' => 60,
                'connect_timeout' => 60
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
}
