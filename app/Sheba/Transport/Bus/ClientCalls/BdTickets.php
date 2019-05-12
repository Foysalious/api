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

//        if (!$token) {
//            $access_token = $this->generateAccessToken();
//            Redis::set('bdticket_bearer_token', $access_token['token'], 'EX', ($access_token['expires'] - 3600));
//        }
        $this->bearerToken = "eyJhbGciOiJIUzUxMiJ9.eyJzdWIiOiIrODgwMTY3ODI0MjkzNCIsImF1ZCI6InVua25vd24iLCJleHAiOjE1NTg5NTg5ODYsInVzZXIiOnsiaWQiOiI1YzcyM2Q0YTVhNzlmYjNmMDgyYmFlOTYiLCJyb2xlSWRlbnRpZmllcnMiOlsiUk9MRV9DVVNUT01FUiIsIlJPTEVfQVBJX0FHRU5UIl0sImZpcnN0TmFtZSI6IlNoZWJhIiwiZ2l2ZW5OYW1lIjoiLnh5eiIsImFsdEZpcnN0TmFtZSI6bnVsbCwiYWx0R2l2ZW5OYW1lIjpudWxsLCJnZW5kZXIiOm51bGwsInBob25lTnVtYmVyIjoiKzg4MDE2NzgyNDI5MzQiLCJzZWNvbmRhcnlQaG9uZU51bWJlciI6bnVsbCwiZW1haWwiOiJ0cEBzaGViYS54eXoiLCJwYXNzd29yZFVwZGF0ZWQiOjE1NTA5OTExNjMsImxvY2FsZSI6IkVOIiwiYWRkcmVzcyI6bnVsbCwic3RhdHVzIjoiUEhPTkVfVkVSSUZJQ0FUSU9OX05FRURFRCIsImVtYWlsVmVyaWZpY2F0aW9uU3RhdHVzIjoiUEVORElORyIsInVzZXJHcm91cERpc3BsYXkiOm51bGwsInVzZXJHcm91cHMiOm51bGwsImZpZWxkQWdlbnREZXRhaWxzIjpudWxsLCJhcHBEZXRhaWxzIjpudWxsLCJjb3Jwb3JhdGVDdXN0b21lckRldGFpbHMiOm51bGwsImZ1bGxOYW1lIjoiU2hlYmEgLnh5eiIsImFjY291bnROb25FeHBpcmVkIjp0cnVlLCJjcmVkZW50aWFsc05vbkV4cGlyZWQiOnRydWUsImFjY291bnROb25Mb2NrZWQiOnRydWUsIm5hbWVPclVzZXJOYW1lIjoiU2hlYmEgLnh5eiIsInVzZXJOaWNrIjoiU2hlYmEiLCJhdXRob3JpdGllcyI6WyJCT09LSU5HX0NSRUFURSIsIk9SREVSX0NSRUFURSIsIlNVUFBPUlRfVElDS0VUX0NSRUFURSIsIlNVUFBPUlRfVElDS0VUX0NSRUFURV9TRUxGIiwiQk9PS0lOR19DUkVBVEVfU0VMRiIsIkJPT0tJTkdfVVBEQVRFX1NFTEYiLCJCT09LSU5HX1ZJRVdfU0VMRiIsIkJPT0tJTkdfTElTVF9TRUxGIiwiQ0FSVF9DUkVBVEVfU0VMRiIsIkNBUlRfVVBEQVRFX1NFTEYiLCJDQVJUX0RFU1RST1lfU0VMRiIsIkNBUlRfVklFV19TRUxGIiwiQ0FSVF9MSVNUX1NFTEYiLCJPUkRFUl9DUkVBVEVfU0VMRiIsIk9SREVSX1VQREFURV9TRUxGIiwiTk9USUZJQ0FUSU9OX0NSRUFURSIsIk9SREVSX0RFU1RST1lfU0VMRiIsIk9SREVSX0xJU1RfU0VMRiIsIlBBWU1FTlRHQVRFV0FZX0JLQVNIX0FMTE9XIiwiUEFZTUVOVEdBVEVXQVlfUE9SVFdBTExFVF9BTExPVyIsIlBBWU1FTlRfUFVSQ0hBU0VfU0VMRiIsIlBBWU1FTlRfUkVDSEFSR0VfU0VMRiIsIlNVUFBPUlRfVElDS0VUX0xJU1RfU0VMRiIsIlNVUFBPUlRfVElDS0VUX1ZJRVdfU0VMRiIsIkFDQ09VTlRTX0NIRUNLX0JBTEFOQ0VfU0VMRiIsIkFDQ09VTlRTX1ZJRVdfVFJBTlNBQ1RJT05TX1NFTEYiLCJPUkRFUl9WSUVXX1NFTEYiLCJPUkRFUl9TRUFSQ0giLCJQUklDSU5HX1NIT1ciLCJPUkRFUl9WSUVXIiwiT1JERVJfTElTVCIsIk9SREVSX1VQREFURSIsIkJPT0tJTkdfVklFVyIsIkJPT0tJTkdfTElTVCIsIkJPT0tJTkdfVVBEQVRFIiwiVVNFUl9WSUVXIiwiQ0hFQ0tfQktBU0hfVFJBTlNBQ1RJT04iLCJCT09LSU5HX0NSRUFURV9PVEhFUlMiLCJCT09LSU5HX0RFU1RST1lfT1RIRVJTIiwiQk9PS0lOR19MSVNUX09USEVSUyIsIkJPT0tJTkdfVVBEQVRFX09USEVSUyIsIkJPT0tJTkdfVklFV19PVEhFUlMiLCJSRUZVTkRfUkVRVUVTVF9DUkVBVEUiXX0sImlhdCI6MTU1NzY2Mjk4Nn0.Eo28ZcDmTLs5Wxu7qAWTUDICh0j-HrxbTCBnqEpclS6zanCaw8TWlW0aEFO7ge9LDPxCFqLbJR3oYhCawWT_iQ";
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