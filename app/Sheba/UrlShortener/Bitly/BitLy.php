<?php namespace Sheba\UrlShortener\Bitly;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class BitLy
{
    private $base_url;
    private $client;
    private $access_token;

    public function __construct()
    {
        $this->base_url = config('bitly.url');
        $this->access_token = config('bitly.access_token');
        $this->client = new Client([
            'base_url' => $this->base_url.'/',
            'headers' => array(
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Host' => $this->base_url,
                'Authorization' => 'Bearer '.$this->access_token
            ),
        ]);
    }

    /**
     * @param $url
     * @param array $params
     * @return \Exception|GuzzleException|mixed|\Psr\Http\Message\ResponseInterface
     * @throws GuzzleException
     */
    public function get($url, $params = array())
    {
        try {
            $response = $this->client->request('GET', $this->base_url . $url, [
                'query' => $params
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    /**
     * @param $url
     * @param $body
     * @throws GuzzleException
     */
    public function post($url, $body)
    {
        try {
            $response = $this->client->request('POST', $this->base_url.$url, [
                'json' => $body
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw $e;
        }
    }
}