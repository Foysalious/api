<?php namespace App\Sheba\MTB;

use App\Exceptions\NotFoundAndDoNotReportException;
use App\Sheba\MTB\Exceptions\MtbServiceServerError;
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
     * @param $auth_type
     * @return mixed
     * @throws MtbServiceServerError
     * @throws NotFoundAndDoNotReportException
     */
    public function get($uri, $auth_type)
    {
        return $this->call('get', $uri, $auth_type);
    }


    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @param bool $multipart
     * @param $auth_type
     * @return mixed
     * @throws MtbServiceServerError
     * @throws NotFoundAndDoNotReportException
     */
    private function call($method, $uri, $auth_type, $data = null, $multipart = false)
    {
        try {
            $response = $this->client->request(strtoupper($method), $this->makeUrl($uri),
                $this->getOptions($auth_type, $data, $multipart))->getBody()->getContents();
            return json_decode($response, true);
        } catch (GuzzleException $e) {
            $res = $e->getResponse();
            $http_code = $res->getStatusCode();
            $message = $res->getBody()->getContents();
            if ($http_code == 404) {
                throw new NotFoundAndDoNotReportException($message, $http_code);
            }
            if ($http_code > 399 && $http_code < 500) throw new MtbServiceServerError($message, $http_code);
            throw new MtbServiceServerError($e->getMessage(), $http_code);
        }
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function generateMtbBearerToken()
    {
        return $this->post('api/token', ['username' => config('mtb.mtb_username'),
            'password' => config('mtb.mtb_password'), 'grant_type' => config('mtb.mtb_grant_type')], AuthTypes::NO_AUTH);
    }

    private function getMtbBearerToken()
    {
        $mtbJwt = Redis::get('mtb_jwt');
        if ($mtbJwt)
            return $mtbJwt;

        else {
            $mtbJwt = $this->generateMtbBearerToken()['access_token'];
            Redis::set('mtb_jwt', $mtbJwt);
            Redis::expire('mtb_jwt', 250);
            return $mtbJwt;
        }
    }

    /**
     * @param $data
     * @param bool $multipart
     * @param $auth_type
     * @return array
     */
    private function getOptions($auth_type, $data = null, $multipart = false): array
    {
        $options['headers'] = [
            'Accept' => 'application/json',
        ];
        if ($auth_type === AuthTypes::BARER_TOKEN) {
            $options['headers'] = (array_merge($options['headers'], ['Authorization' => 'Bearer ' . $this->getMtbBearerToken()]));
        } elseif ($auth_type === AuthTypes::BASIC_AUTH_TYPE) {
            $orgId = config('mtb.sheba_organization_id');
            $username = config('mtb.sheba_username');
            $password = config('mtb.sheba_password');
            $options['headers'] = (array_merge($options['headers'], ['OrgId' => $orgId]));
            $options['auth'] = [$username, $password];
        }

        if (!$data) return $options;
        if ($multipart) {
            $options['multipart'] = $data;
        } else {
            $options['form_params'] = $data;
            $options['json'] = $data;
        }
        return $options;
    }

    public function post($uri, $data, $auth_type, $multipart = false)
    {
        return $this->call('post', $uri, $auth_type, $data, $multipart);
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
