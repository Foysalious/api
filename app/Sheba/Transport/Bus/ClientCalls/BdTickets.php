<?php namespace Sheba\Transport\Bus\ClientCalls;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Redis;
use Psr\Http\Message\ResponseInterface;
use Sheba\Transport\Bus\Exception\UnCaughtClientException;

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
            // $expired_at = $access_token['expires'] - 60;
            Redis::set('bdticket_bearer_token', $access_token['token']);
            Redis::expire('bdticket_bearer_token', 270);
            $token = $access_token['token'];
        }
        $this->bearerToken = $token;
    }

    /**
     * @param $port
     * @return $this
     */
    public function setBookingPort($port)
    {
        $this->bookingPort = $port;
        return $this;

    }

    /**
     * @param $uri
     * @return mixed|ResponseInterface
     * @throws GuzzleException
     * @throws UnCaughtClientException
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
     * @throws UnCaughtClientException
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
     * @throws UnCaughtClientException
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
     * @throws UnCaughtClientException
     */
    private function call($method, $uri, $data = null)
    {
        try {
            $res = $this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data));
            $res = json_decode($res->getBody()->getContents(), true);
            return $res;
        } catch (RequestException $e) {
            $exception = (string) $e->getResponse()->getBody();
            $exception = json_decode($exception);

            throw new UnCaughtClientException($exception->errors[0], 409, $e);
        }
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . ':' . $this->bookingPort . "/" . $this->apiVersion . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options = [];
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