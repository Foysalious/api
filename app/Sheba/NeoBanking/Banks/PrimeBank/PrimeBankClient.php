<?php


namespace App\Sheba\NeoBanking\Banks\PrimeBank;


use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Sheba\NeoBanking\Banks\PrimeBank\ApiClient;
use Sheba\TPProxy\SbsProxyClient;
use Sheba\TPProxy\TPProxyClient;
use Sheba\TPProxy\TPProxyServerError;
use Sheba\TPProxy\TPRequest;

class PrimeBankClient extends ApiClient
{
    protected $client;
    protected $baseUrl;

    public function __construct()
    {
        parent::__construct();
        $this->client = (new Client());
        $this->baseUrl = rtrim(config('neo_banking.prime_bank_sbs_url'));
    }

    /**
     * @param $user
     * @return mixed
     * @throws TPProxyServerError
     */
    public function generateToken($user)
    {
        return $this->get("/");
    }

    /**
     * @param $uri
     * @param array $headers
     * @return mixed
     * @throws TPProxyServerError
     */
    public function get($uri, $headers = [])
    {
        return $this->call('get', $uri, null, $headers);
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @param array $headers
     * @return mixed
     * @throws TPProxyServerError
     */
    private function call($method, $uri, $data = null, $headers=[])
    {
        $options = $data ? $this->getOptions($data) : ['json'=>[]];
        /** @var SbsProxyClient $client */
        $client = app(SbsProxyClient::class);
        if (!isset($options['json'])) {
            return $client->callWithFile($this->makeUrl($uri), strtoupper($method), $options);
        }
        return $client->call((new TPRequest())->setMethod($method)->setInput($options['json'])->setUrl($this->makeUrl($uri))
            ->setHeaders(array_merge(['Content-Type:application/json'], $headers)));
    }

    private function makeUrl($uri)
    {
        return $this->baseUrl . "/" . $uri;
    }

    private function getOptions($data = null)
    {
        $options = [];

        if ($data) {
            $request = request();
            /** @var UploadedFile $id_front */
            /** @var UploadedFile $id_back */
            $id_front = $request->file('id_front');
            $id_back = $request->file('id_back');
            $applicant_photo = $request->file('applicant_photo');
            if ($request->is_kyc_store) {
                $options['multipart'] = [
                    [
                        'name' => 'applicant_photo',
                        'contents' => File::get($applicant_photo->getRealPath()),
                        'filename' => $applicant_photo->getClientOriginalName()
                    ],
                    [
                        'name' => 'mobile_number',
                        'contents' => $request->mobile_number,
                    ],
                    [
                        'name' => 'nid_no',
                        'contents' => $request->nid_no,
                    ],
                    [
                        'name' => 'dob',
                        'contents' => $request->dob,
                    ],
                    [
                        'name' => 'applicant_name_ben',
                        'contents' => $request->applicant_name_ben,
                    ],
                    [
                        'name' => 'applicant_name_eng',
                        'contents' => $request->applicant_name_eng,
                    ],
                    [
                        'name' => 'father_name',
                        'contents' => $request->father_name,
                    ],
                    [
                        'name' => 'mother_name',
                        'contents' => $request->mother_name,
                    ],
                    [
                        'name' => 'spouse_name',
                        'contents' => $request->spouse_name,
                    ],
                    [
                        'name' => 'pres_address',
                        'contents' => $request->pres_address,
                    ],
                    [
                        'name' => 'perm_address',
                        'contents' => $request->perm_address,
                    ],
                    [
                        'name' => 'id_front_name',
                        'contents' => $request->id_front_name,
                    ],
                    [
                        'name' => 'id_back_name',
                        'contents' => $request->id_back_name,
                    ],
                    [
                        'name' => 'is_kyc_store',
                        'contents' => true,
                    ]
                ];
            } else {
                if ($id_front && $id_back) {
                    $options['multipart'] = [
                        [
                            'name' => 'id_front',
                            'contents' => File::get($id_front->getRealPath()),
                            'filename' => $id_front->getClientOriginalName()
                        ],
                        [
                            'name' => 'id_back',
                            'contents' => File::get($id_back->getRealPath()),
                            'filename' => $id_back->getClientOriginalName()
                        ]
                    ];
                } else {
                    $options['json'] = $data;
                }
            }
        }
        return $options;
    }

    /**
     * @param $method
     * @param $uri
     * @param null $data
     * @return mixed
     * @throws Exception
     */
    public function create($method, $uri, $data = null)
    {
        $headers=['CLIENT-ID:'. config('neo_banking.sbs_client_id'), 'CLIENT-SECRET:'.  config('neo_banking.sbs_client_secret')];
        return $this->call($method,$uri,$data,$headers);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     * @throws TPProxyServerError
     */
    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

    /**
     * @param $uri
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function createAccount($uri, $data)
    {
        return $this->create('post', $uri, $data);
    }



}
