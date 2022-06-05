<?php namespace App\Sheba\MTB;

use App\Exceptions\NotFoundAndDoNotReportException;
use App\Sheba\MTB\Exceptions\MtbServiceServerError;
use App\Sheba\QRPayment\QRPaymentStatics;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Redis;
use Mpdf\Tag\Q;
use Sheba\ModificationFields;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPRequest;

class MtbServerClient
{
    use ModificationFields;

    protected $tpClient;
    public $baseUrl;

    public function __construct(TPProxyClient $tp_client)
    {
        $this->tpClient = $tp_client;
        $this->baseUrl = rtrim(config('mtb.api_url'), '/');
    }

    /**
     * @param $uri
     * @param $auth_type
     * @return mixed
     * @throws TPProxyServerError
     */
    public function get($uri, $auth_type)
    {
        list($headers, $auth) = $this->getHeadersAndAuth($auth_type);
        $request = (new TPRequest())->setUrl($uri)->setMethod(TPRequest::METHOD_POST)->setHeaders($headers)->setAuth($auth);
        return $this->tpClient->call($request);
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

    private function makeUrl($uri): string
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function generateMtbBearerToken()
    {
        return $this->post(QRPaymentStatics::MTB_TOKEN_GENERATE, ['username' => config('mtb.mtb_username'),
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
    private function getOptions($data = null, $multipart = false): array
    {
        $options = [];
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
     * @throws TPProxyServerError
     */
    public function post($uri, $data, $auth_type, $multipart = false)
    {
        $data = $this->getOptions($data, $multipart);
        list($headers, $auth) = $this->getHeadersAndAuth($auth_type);
        $request = (new TPRequest())->setUrl($uri)->setMethod(TPRequest::METHOD_POST)->setInput($data)->setAuth($auth)->setHeaders($headers);
        return $this->tpClient->call($request);
    }

    private function getHeadersAndAuth($auth_type): array
    {
        $headers = ['Accept' => 'application/json'];
        $auth = [];
        if ($auth_type === AuthTypes::BARER_TOKEN) {
            $headers = (array_merge($headers, ['Authorization' => 'Bearer ' . $this->getMtbBearerToken()]));
        } elseif ($auth_type === AuthTypes::BASIC_AUTH_TYPE) {
            $orgId = config('mtb.sheba_organization_id');
            $username = config('mtb.sheba_username');
            $password = config('mtb.sheba_password');
            $headers = (array_merge($headers, ['OrgId' => $orgId]));
            $auth = [$username, $password];
        }
        return [$headers, $auth];
    }


}
