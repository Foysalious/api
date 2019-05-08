<?php namespace Sheba\Transport\Bus\ClientCalls;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Redis;
use Psr\Http\Message\ResponseInterface;

class BdTickets extends ExternalApiClient
{
    protected $client;
    protected $baseUrl;
    protected $apiVersion;
    protected $bookingPort;
    private $authorizationPort;
    private $bearerToken;

    /**
     * BdTickets constructor.
     */
    public function __construct()
    {
        $token = Redis::get('bdticket_bearer_token');
        $this->baseUrl = config('bus_transport.bdticket.base_url');
        $this->apiVersion = config('bus_transport.bdticket.api_version');
        $this->bookingPort = config('bus_transport.bdticket.booking_port');
        $this->authorizationPort = config('bus_transport.bdticket.authorization_port');
        $this->client = (new Client(['headers' => ['Content-Type' => 'application/json']]));

        if (!$token) {
            $access_token = $this->generateAccessToken();
            Redis::set('bdticket_bearer_token', $access_token['token'], 'EX', ($access_token['expires'] - 3600));
        }
        $this->bearerToken = $token;
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
     * @param $uri
     * @param $data
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     */
    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
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
        if (!in_array($res->getStatusCode(), [200, 201])) throw new Exception();
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
        // if (!is_null($data)) $options['form_params'] = $data;
        $options['headers'] = ['Authorization' => 'Bearer ' . $this->bearerToken];
        $options['body'] = json_encode( $data);

        return $options;
    }

    private function generateAccessToken()
    {
        $url    = $this->baseUrl . ':' . $this->authorizationPort . "/" . $this->apiVersion . "/auth";
        $data   = ['email' => config('bus_transport.bdticket.login_email'), 'password' => config('bus_transport.bdticket.login_pass')];
        $res    = $this->client->request('POST', $url, $this->getOptions($data));
        $res    = json_decode($res->getBody()->getContents(), true);

        return $res['data']['access'];
    }
}