<?php


namespace App\Sheba\NeoBanking\Banks\PrimeBank;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;

class PrimeBankClient
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        $this->client  = (new Client());
        $this->baseUrl = rtrim(config('neo_banking.prime_bank_sbs_url'));
    }

    public function generateToken($user)
    {
        return $this->get("/");
    }

    public function get($uri)
    {
        return $this->call('get', $uri);
    }

    private function call($method, $uri, $data = null)
    {
        $options = $data ? $this->getOptions($data) : [];

        try {
            $res = $this->client->request(strtoupper($method), $this->makeUrl($uri), $options);
            $res = json_decode($res->getBody()->getContents(), true);
            if ($res['code'] != 200) throw new Exception($res['message'],$res['code']);

            unset($res['code'], $res['message']);
            return $res;
        } catch (GuzzleException $e) {
            dd($e->getMessage());
        }
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $request = request();
        /** @var UploadedFile $file */
        $id_front = $request->file('id_front');
        $id_back = $request->file('id_back');
        $options['multipart'] = [
            ['name' => 'id_front', 'contents' => File::get($id_front->getRealPath()), 'filename' => $id_front->getClientOriginalName()],
            ['name' => 'id_back', 'contents' => File::get($id_back->getRealPath()), 'filename' => $id_back->getClientOriginalName()]
        ];
        return $options;
    }

    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

}