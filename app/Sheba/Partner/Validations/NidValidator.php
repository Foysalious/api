<?php namespace Sheba\Partner\Validations;


use App\Sheba\Partner\Validations\NidValidationResponse;
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
    abstract function check($nid, $fullName = null, $dob = null):NidValidationResponse;
}
