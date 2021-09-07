<?php namespace App\Sheba\PosCustomerService;


use App\Sheba\PosCustomerService\Exceptions\SmanagerUserServiceServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class SmanagerUserServerClient
{
    protected $client;
    protected $baseUrl;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('smanager_user_service.api_url'), '/');
    }

    public function get($uri)
    {
        return $this->call('get', $uri);
    }


    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @param bool $multipart
     * @return mixed
     * @throws SmanagerUserServiceServerError
     */
    private function call($method, $uri, $data = null, $multipart = false)
    {dd($this->makeUrl($uri));
        try {
            return json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data, $multipart))->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = $res->getBody()->getContents();
            if ($http_code > 399 && $http_code < 500) throw new SmanagerUserServiceServerError($message, $http_code);
            throw new SmanagerUserServiceServerError($e->getMessage(), $http_code);
        }
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null, $multipart = false)
    {
        $options['headers'] = [
            'Accept' => 'application/x-www-form-urlencoded'
        ];
        if (!$data) return $options;
        if ($multipart) {
            $options['multipart'] = $data;
        } else {
            $options['form_params'] = $data;
            $options['json'] = $data;
        }
        return $options;
    }

    public function post($uri, $data, $multipart = false)
    {
        return $this->call('post', $uri, $data, $multipart);
    }


    /**
     * @param $uri
     * @param $data
     * @param false $multipart
     * @return mixed
     * @throws SmanagerUserServiceServerError
     */
    public function put($uri, $data, $multipart = false)
    {
        return $this->call('put', $uri, $data, $multipart);
    }


    /**
     * @param $uri
     * @return mixed
     * @throws SmanagerUserServiceServerError
     */
    public function delete($uri)
    {
        return $this->call('DELETE', $uri);
    }
}
