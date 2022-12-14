<?php namespace App\Sheba\PosOrderService;


use App\Exceptions\NotFoundAndDoNotReportException;
use App\Sheba\InventoryService\Exceptions\InventoryServiceServerError;
use App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Sheba\ModificationFields;

class PosOrderServerClient
{
    use ModificationFields;

    protected $client;
    public $baseUrl;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('pos_order_service.api_url'), '/');
    }

    /**
     * @param $uri
     * @return mixed
     * @throws PosOrderServiceServerError
     */
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
     * @throws PosOrderServiceServerError
     * @throws NotFoundAndDoNotReportException
     */
    private function call($method, $uri, $data = null, $multipart = false)
    {
        try {
            return json_decode($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data, $multipart))->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = $res->getBody()->getContents();
            if($http_code == 404) {
                throw new NotFoundAndDoNotReportException($message, $http_code);
            }
            if ($http_code > 399 && $http_code < 500) throw new PosOrderServiceServerError($message, $http_code);
            throw new PosOrderServiceServerError($e->getMessage(), $http_code);
        }
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null, $multipart = false)
    {
        if (isset(getShebaRequestHeader()->toArray()['portal-name'])) {
            $portal_name = getShebaRequestHeader()->toArray()['portal-name'];
        } else {
            $portal_name = null;
        }
        if (isset(getShebaRequestHeader()->toArray()['Version-Code'])) {
            $version_code = getShebaRequestHeader()->toArray()['Version-Code'];
        } else {
            $version_code = null;
        }
        $options['headers'] = [
            'Accept' => 'application/json',
            'portal-name' => $portal_name,
            'Version-Code' => $version_code,
            'Modifier-Name' => $this->getModifierNameForHeader()
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
     * @param bool $multipart
     * @return array|object|string|null
     * @throws PosOrderServiceServerError
     */
    public function put($uri, $data, $multipart = false)
    {
        return $this->call('put', $uri, $data, $multipart);
    }

    /**
     * @param $uri
     * @return array|object|string|null
     * @throws PosOrderServiceServerError
     */
    public function delete($uri)
    {
        return $this->call('DELETE', $uri);
    }

    private function getModifierNameForHeader()
    {
        if ($manager_resource = \request()->manager_resource) {
            $this->setModifier($manager_resource);
            return $this->getModifierName();
        } else {
            return '';
        }
    }


}
