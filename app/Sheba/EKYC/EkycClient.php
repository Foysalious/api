<?php namespace Sheba\EKYC;

use GuzzleHttp\Client;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Sheba\EKYC\Exceptions\EkycServerError;
use Throwable;

class EkycClient
{
    protected $userId;
    protected $clientId;
    protected $clientSecret;
    protected $userType;
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->client = (new Client());
        $this->baseUrl = rtrim(config('ekyc.url', 'http://ekyc.sheba.test:8181') . '/api/v1');
        $this->clientId = config('ekyc.client_id');
        $this->clientSecret = config('ekyc.client_secret');
    }

    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     * @throws EkycServerError
     * @throws GuzzleException
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return mixed
     * @throws EkycServerError
     * @throws GuzzleException
     */
    private function call($method, $uri, $data = null)
    {
        try {
            $res = $this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data));
            $res = json_decode($res->getBody()->getContents(), true);
            if ($res['code'] != 200)
                throw new EkycServerError($res['message'], $res['code']);
            unset($res['code'], $res['message']);
            return $res;
        } catch (EkycServerError $exception) {
            Log::info($exception);
        }
    }

    private function makeUrl($uri): string
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options['headers'] = [
            'Accept' => 'application/json',
            'CLIENT-ID' => $this->clientId,
            'CLIENT-SECRET' => $this->clientSecret
        ];
        $options['http_errors'] = false;
        $options=array_merge($options, [
            'read_timeout'    => 300,
            'connect_timeout' => 120, 'timeout' => 120
        ]);

        if (isset($data['id_front']) && $data['id_back']) {
            /** @var UploadedFile $id_front */
            /** @var UploadedFile $id_back */
            $id_front               = $data['id_front'];
            $id_back                = $data['id_back'];

            $options['multipart'] = [
                ['name' => 'id_front', 'contents' => File::get($id_front->getRealPath()), 'filename' => $id_front->getClientOriginalName()],
                ['name' => 'id_back', 'contents' => File::get($id_back->getRealPath()), 'filename' => $id_back->getClientOriginalName()]
            ];

        } else {
            $nid = $data['nid'];
            $person_photo = $data['pro_pic'];
            $dob = $data['dob'];

            $options['form_params'] = [
                'nid' => $nid,
                'person_photo' => $person_photo,
                'dob' => $dob,
                'selfie_photo' => $data['selfie_photo']
            ];
        }

        return $options;
    }

    public function setUserType($userType)
    {
        $this->userType = $userType;
        return $this;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }
}
