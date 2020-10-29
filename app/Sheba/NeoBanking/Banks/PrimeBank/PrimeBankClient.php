<?php


namespace App\Sheba\NeoBanking\Banks\PrimeBank;


use Exception;
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
        $options =[];

        if($data){
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
                        'name' => 'gender',
                        'contents' => $request->gender,
                    ],
                    [
                        'name' => 'nominee',
                        'contents' => $request->nominee,
                    ],
                    [
                        'name' => 'profession',
                        'contents' => $request->profession,
                    ],
                    [
                        'name' => 'nominee_relation',
                        'contents' => $request->nominee_relation,
                    ],
                    [
                        'name' => 'is_kyc_store',
                        'contents' => true,
                    ]
                ];
            } else {
                if($id_front && $id_back) {
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
                    $options['json']        = $data;
                }
            }
        }
        return $options;
    }

    public function post($uri, $data)
    {
        return $this->call('post', $uri, $data);
    }

}