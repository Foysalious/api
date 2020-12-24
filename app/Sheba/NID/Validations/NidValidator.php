<?php namespace Sheba\NID\Validations;


use App\Sheba\NID\Validations\NidValidationResponse;
use GuzzleHttp\Client;

abstract class NidValidator
{
    protected $client;

    /**
     * NidValidator constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param $nid
     * @param null $fullName
     * @param null $dob
     * @return NidValidationResponse
     */
    abstract public function check($nid, $fullName = null, $dob = null):NidValidationResponse;
}
