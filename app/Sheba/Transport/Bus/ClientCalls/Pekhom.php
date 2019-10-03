<?php namespace Sheba\Transport\Bus\ClientCalls;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Pekhom extends ExternalApiClient
{
    protected $client;
    protected $baseUrl;
    private $userName;
    private $apiKey;

    /**
     * BdTickets constructor.
     */
    public function __construct()
    {
        $this->client = (new Client(['headers' => ['Content-Type' => 'application/json']]));
        $this->baseUrl = config('bus_transport.pekhom.base_url');
        $this->userName = config('bus_transport.pekhom.user_name');
        $this->apiKey = config('bus_transport.pekhom.api_key');
    }

    /**
     * @param $uri
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    private function call($method, $uri, $data = null)
    {
        $res = $this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data));
        if ($res->getStatusCode() != 200) throw new Exception();
        $res = json_decode($res->getBody()->getContents(), true);

        unset($res['code'], $res['message']);
        return $res;
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options = [];
        // if (!is_null($data)) $options['form_params'] = $data;
        $data['username']  = $this->userName;
        $data['api_key'] = $this->apiKey;
        $options['body'] = json_encode($data);

        return $options;
    }
}