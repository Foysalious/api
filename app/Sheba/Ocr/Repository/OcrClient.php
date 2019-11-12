<?php namespace Sheba\Ocr\Repository;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Sheba\Ocr\Exceptions\OcrServerError;

class OcrClient
{
    protected $client;
    protected $baseUrl;
    protected $apiKey;

    /**
     * OcrClient constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->baseUrl = rtrim(config('ocr.api_url'), '/');
        $this->apiKey = config('ocr.api_key');
    }

    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return array
     * @throws OcrServerError
     */
    private function call($method, $uri, $data = null)
    {
        try {
            $res = decodeGuzzleResponse($this->client->request(strtoupper($method), $this->makeUrl($uri), $this->getOptions($data)));
            if ($res['code'] != 200) throw new OcrServerError($res['message']);
            unset($res['code'], $res['message']);
            return $res;
        } catch (GuzzleException $e) {
            $res = decodeGuzzleResponse($e->getResponse());
            if ($res['code'] == 404) throw new OcrServerError($res['message']);
            throw new OcrServerError($e->getMessage());
        }
    }

    /**
     * @param $uri
     * @return string
     */
    private function makeUrl($uri)
    {
        return $this->baseUrl . $uri;
    }

    /**
     * @param null $data
     * @return mixed
     */
    private function getOptions($data = null)
    {
        $options['headers'] = [
            'x-api-key' => $this->apiKey,
            'Accept' => 'application/json'
        ];

        if ($data) {
            $request = request();
            /** @var UploadedFile $file */
            $file = $request->file('nid_image');
            $options['multipart'] = [
                ['name' => 'side', 'contents' => $request->get('side')],
                ['name' => 'nid_image', 'contents' => File::get($file->getRealPath()), 'filename' => $file->getClientOriginalName()]
            ];
        }
        return $options;
    }

    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    public function put($uri, $data)
    {
        return $this->call('put', $uri, $data);
    }
}
