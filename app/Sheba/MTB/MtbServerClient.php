<?php namespace App\Sheba\MTB;

use App\Exceptions\NotFoundAndDoNotReportException;
use App\Sheba\MTB\Exceptions\MtbServiceServerError;
use App\Sheba\PosOrderService\Exceptions\PosOrderServiceServerError;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Redis;
use Sheba\ModificationFields;

class MtbServerClient
{
    use ModificationFields;

    protected $client;
    public $baseUrl;

    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('mtb.api_url'), '/');
    }

    /**
     * @param $uri
     * @return mixed
     * @throws MtbServiceServerError
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
     * @throws MtbServiceServerError
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
            if ($http_code == 404) {
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

    private function generateMtbBearerToken()
    {
        return $this->post('api/token', ['username' => config('mtb.mtb_username'),
            'password' => config('mtb.mtb_password'), 'grant_type' => config('mtb.mtb_grant_type')]);
    }

    private function getMtbBearerToken()
    {
        $mtbJwt = Redis::get('mtb_jwt');
        if ($mtbJwt)
            return $mtbJwt;
        else {
            $mtbJwt = $this->generateMtbBearerToken();
            Redis::set('mtb_jwt', $mtbJwt);
            Redis::expire('mtb_jwt', 250);
            return $mtbJwt;
        }
    }

    private function getOptions($data = null, $multipart = false)
    {
        $options['headers'] = [
            'Accept' => 'application/json',
            'Authorization' => 'Bearer ' . $this->getMtbBearerToken(),
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
     * @throws MtbServiceServerError|NotFoundAndDoNotReportException
     */
    public function put($uri, $data, $multipart = false)
    {
        return $this->call('put', $uri, $data, $multipart);
    }

    /**
     * @param $uri
     * @return array|object|string|null
     * @throws MtbServiceServerError|NotFoundAndDoNotReportException
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
