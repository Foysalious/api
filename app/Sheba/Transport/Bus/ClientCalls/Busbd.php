<?php namespace Sheba\Transport\Bus\ClientCalls;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

class Busbd extends ExternalApiClient
{
    protected $client;
    protected $baseUrl;
    protected $apiVersion;
    protected $bookingPort;

    /**
     * Busbd constructor.
     */
    public function __construct()
    {
        $this->client = (new Client(['headers' => ['Content-Type' => 'application/json']]));
        $this->baseUrl = config('bus_transport.busbd.base_url');
        $this->apiVersion = config('bus_transport.busbd.api_version');
        $this->bookingPort = config('bus_transport.busbd.booking_port');
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
        return $this->baseUrl . ':' . $this->bookingPort . "/" . $this->apiVersion . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options = [];
        //if (!is_null($data)) $options['form_params'] = $data;
        $options['body'] = json_encode( $data);

        return $options;
    }
}