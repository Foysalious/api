<?php namespace Sheba\Bkash\Modules\Tokenized\Methods\Agreement\Responses;


class ExecuteResponse
{
    private $agreementID;

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
        return $this->agreementID;
    }

    /**
     * @param mixed $agreementID
     * @return ExecuteResponse
     */
    public function setAgreementID($agreementID)
    {
        $this->agreementID = $agreementID;
        return $this;
    }

    public function isSuccess()
    {
        return $this->response->statusCode && $this->response->statusCode == "0000";
    }

}