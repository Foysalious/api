<?php namespace App\Sheba\Bondhu\Repository;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\Bondhu\Exeptions\NidOcrServerError;

class OcrClient
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;

    /**
     * OcrClient constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('ocr.api_url'), '/');
        $this->apiKey = config('ocr.api_key');
    }


    /**
     * @param $uri
     * @return array
     */
    public function get($uri)
    {
        return $this->call('get', $uri);
    }


    /**
     * @param $uri
     * @param $data
     * @return array
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }


    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return array
     */
    private function call($method, $uri, $data = null)
    {
        try {
            $res = decodeGuzzleResponse($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));
            dd($res);
            if ($res['code'] != 200) throw new NidOcrServerError($res['message']);
            unset($res['code'], $res['message']);
            return $res;
        } catch (GuzzleException $e) {
            dd($e);
            $res = decodeGuzzleResponse($e->getResponse());
            if ($res['code'] == 400) throw new NidOcrServerError($res['message']);
            throw new NidOcrServerError($e->getMessage());
        }
    }

    /**
     * @param $uri
     * @return string
     */
    private function makeUrl($uri)
    {
        return $this->baseUrl . $uri;
    }

    /**
     * @param null $data
     * @return mixed
     */
    private function getOptions($data = null)
    {
        $options['headers'] = ['Content-Type' => 'multipart/form-data', 'x-api-key' => $this->apiKey, 'Accept' => 'application/json'];
        if ($data) {
            $options['form_params'] = $data;
        }
        return $options;
    }

}
