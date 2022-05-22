<?php namespace Sheba\UrlShortener\Sheba;

use App\Sheba\UrlShortener\Sheba\UrlShortenerServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class UrlShortenerServerClient
{
    protected $client;
    protected $baseUrl;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('url_shortener.api_url'), '/');
    }


    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @param false $multipart
     * @return mixed
     * @throws GuzzleException
     */
    private function call($method, $uri, $data = null, $multipart = false)
    {
        return json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data, $multipart))->getBody()->getContents(), true);
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null, $multipart = false)
    {
        $options['headers'] = [
            'Accept' => 'application/json'
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

    /**
     * @throws UrlShortenerServerError
     */
    public function post($uri, $data, $multipart = false)
    {
        try {
            return $this->call('post', $uri, $data, $multipart);
        } catch (GuzzleException $e) {
            throw new UrlShortenerServerError($e->getMessage(), $e->getCode());
        }
    }

}
