<?php namespace Sheba\NID\Validations;


use App\Sheba\NID\Validations\NidValidationResponse;
use Exception;

class Porichoy extends NidValidator
{
    private $baseUrl;
    private $key;

    /**
     * Porichoy constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->baseUrl = env('PORICHOY_URL', 'https://porichoy.azurewebsites.net/api/kyc/test-');
        $this->key     = env('PORICHOY_KEY');
    }

    /**
     * @param      $nid
     * @param null $fullName
     * @param null $dob
     * @return NidValidationResponse
     * @throws Exception
     */
    public function check($nid, $fullName = null, $dob = null): NidValidationResponse
    {
        try {
            $data     = [
                'national_id'     => $nid,
                'person_fullname' => $fullName,
                'person_dob'      => $dob,
                "match_name"      => true
            ];
            $response = $this->client->post("{$this->baseUrl}nid-person", [
                'headers'     => [
                    'Content-Type' => 'application/json',
                    'x-api-key' => $this->key,
                    'Accept'    => 'application/json',
                ],
                'form_params' => $data,
                'json'        => $data
            ])->getBody()->getContents();
        } catch (\Throwable $e) {
            throw new Exception($e->getMessage(),500);
        }
        $responseType = (new NidValidationResponse())->setFromStringResponse($response, 'passKyc', 'errorCode');
        $responseType->setStatus($responseType->getError() ? 0 : 1);
        return $responseType;
    }
}
