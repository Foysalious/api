<?php namespace App\Sheba\InventoryService\Repository;

use App\Sheba\InventoryService\Exceptions\InventoryServiceServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\ExpenseTracker\Exceptions\ExpenseTrackingServerError;

class InventoryServiceClient
{
    protected $client;
    protected $baseUrl;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('inventory_service.api_url'), '/');
    }

    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    private function call($method, $uri, $data = null)
    {
        try {

            $res = decodeGuzzleResponse($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));

           /* if ($res['code'] != 200)
                throw new InventoryServiceServerError($res['message']);*/
            unset($res['code'], $res['message']);
            return $res;
        } catch (GuzzleException $e) {
            $res = decodeGuzzleResponse($e->getResponse());
            if ($res['code'] == 400)
                throw new InventoryServiceServerError($res['message']);
            throw new InventoryServiceServerError($e->getMessage());
        }
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options['headers'] = [
            'Content-Type' => 'application/json',
            'Accept'       => 'application/json'
        ];
        if ($data) {
            $options['form_params'] = $data;
            $options['json']        = $data;
        }
        return $options;
    }

    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    /**
     * @param $uri
     * @param $data
     * @return array|object|string|null
     * @throws ExpenseTrackingServerError
     */
    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }

    /**
     * @param $uri
     * @return array|object|string|null
     * @throws ExpenseTrackingServerError
     */
    public function delete($uri)
    {
        return $this->call('DELETE', $uri);
    }


}