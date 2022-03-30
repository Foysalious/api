<?php namespace Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses;


class ExecuteResponse
{
    private $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getAgreementID()
    {
        return $this->response->agreementID;
    }

    public function isSuccess()
    {
        return $this->response->statusCode && $this->response->statusCode == "0000";
    }

}